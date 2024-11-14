<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Resources\ProjectResource;
use App\Http\Requests\StoreUpdateProjectRequest;


class ProjectController extends Controller
{
    public function index()
    {
        return ProjectResource::collection(Project::get());
    }

    public function show(Project $project)
    {
        return new ProjectResource($project);
    }


    public function store(StoreUpdateProjectRequest $request)
    {
        $project = new Project();
        $project->fill($request->validated());
        $project->created_by_id = random_int(1, 20);
        $project->save();

        return new ProjectResource($project);
    }

    public function update(StoreUpdateProjectRequest $request, Project $project)
    {
        $project->fill($request->validated());
        $project->save();

        return new ProjectResource($project);
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return response()->json(null, 204);
    }
}
