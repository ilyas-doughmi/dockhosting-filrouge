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

    public function show(Project $project, Request $request)
    {
        $path = $this->checkAccessAndGetPath($project, $request->query('path'));
        if (!File::exists($path) || !File::isFile($path)) {
            return response()->json(['success' => false, 'message' => 'File not found'], 404);
        }

        return response()->json(['success' => true, 'content' => File::get($path)]);
    }

    public function store(Project $project, Request $request)
    {
        $path = $this->checkAccessAndGetPath($project, $request->path);
        
        if ($request->type === 'folder') {
            File::makeDirectory($path, 0755, true);
        } else {
            File::put($path, ''); 
        }

        return response()->json(['success' => true, 'message' => ucfirst($request->type) . ' created']);
    }

    public function update(Project $project, Request $request)
    {
        $path = $this->checkAccessAndGetPath($project, $request->path);
        
        File::put($path, $request->content); 

        return response()->json(['success' => true, 'message' => 'File saved successfully']);
    }

    public function destroy(Project $project, Request $request)
    {
        $path = $this->checkAccessAndGetPath($project, $request->path);

        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        } elseif (File::isFile($path)) {
            File::delete($path);
        }

        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }

    public function upload(Project $project, Request $request)
    {
        $folderPath = $this->checkAccessAndGetPath($project, $request->path ?? ''); 
        
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $file->move($folderPath, $file->getClientOriginalName());
            return response()->json(['success' => true, 'message' => 'File uploaded successfully']);
        }

        return response()->json(['success' => false, 'message' => 'No file provided'], 400);
    }
}