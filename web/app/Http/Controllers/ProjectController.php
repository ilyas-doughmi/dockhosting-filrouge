<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use App\Jobs\SetupProjectContainer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{

    public function index()
    {
        $user = Auth::guard('api')->user();

        $projects = $user->projects()->with('technology')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $projects
        ]);
    }

    public function store(StoreProjectRequest $request)
    {
        $user = Auth::guard('api')->user();
        $slug = Str::slug($request->name);

        $project = Project::create([
            'user_id' => $user->id,
            'technology_id' => $request->technology_id,
            'name' => $request->name,
            'slug' => $slug,
            'directory_path' => "/var/www/dockhosting/projects/{$slug}",
            'url' => "http://{$slug}.localhost",
            'status' => 'pending',
        ]);

        SetupProjectContainer::dispatch($project);

        return response()->json([
            'success' => true,
            'message' => 'Project creation started! It will be online shortly.',
            'data' => $project
        ], 202); 
    }
}