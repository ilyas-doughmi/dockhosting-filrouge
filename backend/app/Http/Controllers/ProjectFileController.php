<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

class ProjectFileController extends Controller
{
    private function checkAccessAndGetPath(Project $project, $requestedPath)
    {
        if ($project->user_id !== Auth::guard('api')->id()) {
            abort(403, 'Unauthorized');
        }

        $basePath = $project->directory_path;
        $safePath = str_replace('..', '', $requestedPath); 
        
        return $basePath . '/' . ltrim($safePath, '/');
    }

    public function index(Project $project, Request $request)
    {
        $path = $this->checkAccessAndGetPath($project, $request->query('path', ''));
        if (!File::exists($path)) return response()->json(['success' => false, 'message' => 'Path not found'], 404);

        $directories = collect(File::directories($path))->map(function($dir) use ($project) {
            return ['name' => basename($dir), 'type' => 'folder', 'path' => str_replace($project->directory_path . '/', '', $dir)];
        });

        $files = collect(File::files($path))->map(function($file) use ($project) {
            return ['name' => $file->getFilename(), 'type' => 'file', 'path' => str_replace($project->directory_path . '/', '', $file->getPathname())];
        });

        return response()->json(['success' => true, 'data' => $directories->merge($files)]);
    }

}