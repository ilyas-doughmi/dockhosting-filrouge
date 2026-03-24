<?php

namespace App\Jobs;

use App\Models\Project;
use App\Services\DockerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SetupProjectContainer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

 
    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public Project $project
    ) {}

    public function handle(DockerService $docker): void
    {
        Log::info("Starting Docker build for project: {$this->project->name}");

        try {
            $container = $docker->createContainer($this->project);

            Log::info("Project '{$this->project->name}' is ONLINE at {$this->project->url} (port {$container->port})");

        } catch (\Throwable $e) {
            Log::error("Docker build FAILED for project '{$this->project->name}': {$e->getMessage()}");

            $this->project->update(['status' => 'error']);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error("Job permanently failed for project '{$this->project->name}': {$exception->getMessage()}");

        $this->project->update(['status' => 'error']);
    }
}