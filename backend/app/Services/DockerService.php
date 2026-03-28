<?php

namespace App\Services;

use App\Models\Container;
use App\Models\Project;
use App\Models\Technology;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\File;

class DockerService
{
    protected string $projectsBasePath;
    protected int $portRangeStart = 10000;
    protected int $portRangeEnd = 60000;

    public function __construct()
    {
        $this->projectsBasePath = config('dockhosting.projects_path', storage_path('projects'));
    }

    public function createContainer(Project $project): Container
    {
        $technology = $project->technology;
        $containerName = 'dockhosting-' . $project->slug;

        $projectDir = $this->setupWorkspace($project->slug, $technology);

        $hostPort = $this->findAvailablePort();

        $dockerId = $this->launchDockerProcess($containerName, $technology, $projectDir, $hostPort);
        return $this->recordInDatabase($project, $dockerId, $containerName, $hostPort, $projectDir);
    }

    public function stopContainer(Container $container): bool
    {
        return $this->executeDockerCommand("docker stop {$container->container_name}", $container, 'stopped');
    }

    public function startContainer(Container $container): bool
    {
        return $this->executeDockerCommand("docker start {$container->container_name}", $container, 'running');
    }

    public function removeContainer(Container $container): bool
    {
        $success = Process::run("docker rm -f {$container->container_name}")->successful();

        if ($success) {
            $container->update(['status' => 'removed']);
            $container->project->update(['status' => 'stopped']);
            Log::info("Container {$container->container_name} removed");
        }

        return $success;
    }

    public function inspectStatus(Container $container): ?string
    {
        $result = Process::run("docker inspect --format=\"{{.State.Status}}\" {$container->container_name}");
        return $result->successful() ? trim($result->output()) : null;
    }

    public function deleteProjectResources(Project $project): void
    {
        $container = $project->container;
        if ($container) {
            Process::run("docker rm -f {$container->container_name}");
            $container->delete();
        }

        if ($project->directory_path && File::isDirectory($project->directory_path)) {
            File::deleteDirectory($project->directory_path);
            Log::info("Deleted directory: {$project->directory_path}");
        }
    }

    private function setupWorkspace(string $slug, Technology $technology): string
    {
        $projectDir = $this->projectsBasePath . DIRECTORY_SEPARATOR . $slug;

        if (!is_dir($projectDir)) {
            mkdir($projectDir, 0755, true);
        }

        $filePath = $projectDir . DIRECTORY_SEPARATOR . $technology->default_file;
        if (!file_exists($filePath)) {
            file_put_contents($filePath, $technology->default_content);
        }

        return $projectDir;
    }

    private function launchDockerProcess(string $name, Technology $tech, string $dir, int $port): string
    {
        $mountPath = str_replace('\\', '/', $dir);
        $target = str_contains($tech->docker_image, 'node') ? '/usr/src/app' : '/var/www/html';

        $cmd = "docker run -d --name {$name} -p {$port}:{$tech->default_port} -v \"{$mountPath}:{$target}\" ";

        if (str_contains($tech->docker_image, 'node')) {
            $cmd .= "-w {$target} {$tech->docker_image} node {$tech->default_file}";
        } else {
            $cmd .= $tech->docker_image;
        }

        Log::info("Running Docker: {$cmd}");
        
        $result = Process::run($cmd);

        if ($result->failed()) {
            throw new \RuntimeException("Docker run failed: " . $result->errorOutput());
        }

        return trim($result->output());
    }

    private function recordInDatabase(Project $project, string $dockerId, string $name, int $port, string $dir): Container
    {
        $container = Container::create([
            'project_id'     => $project->id,
            'container_id'   => $dockerId,
            'container_name' => $name,
            'image'          => $project->technology->docker_image,
            'port'           => $port,
            'internal_port'  => $project->technology->default_port,
            'status'         => 'running',
            'started_at'     => now(),
        ]);

        $project->update([
            'url'            => "http://localhost:{$port}",
            'directory_path' => $dir,
            'status'         => 'online',
        ]);

        Log::info("Container {$name} successfully saved to database on port {$port}");

        return $container;
    }

    private function executeDockerCommand(string $command, Container $container, string $newStatus): bool
    {
        $result = Process::run($command);

        if ($result->successful()) {
            $container->update([
                'status' => $newStatus,
                'started_at' => $newStatus === 'running' ? now() : $container->started_at,
                'stopped_at' => $newStatus === 'stopped' ? now() : null,
            ]);
            
            $container->project->update(['status' => $newStatus === 'running' ? 'online' : 'stopped']);
            
            Log::info("Container {$container->container_name} status changed to {$newStatus}");
            return true;
        }

        Log::error("Docker command failed: {$command} - Error: {$result->errorOutput()}");
        return false;
    }

    private function findAvailablePort(): int
    {
        $usedPorts = Container::whereIn('status', ['running', 'pending'])->pluck('port')->toArray();

        for ($port = $this->portRangeStart; $port <= $this->portRangeEnd; $port++) {
            if (!in_array($port, $usedPorts)) {
                if ($connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1)) {
                    fclose($connection);
                    continue;
                }
                return $port;
            }
        }

        throw new \RuntimeException('No available ports found.');
    }
}