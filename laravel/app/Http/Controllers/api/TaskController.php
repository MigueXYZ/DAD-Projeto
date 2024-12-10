<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Resources\TaskResource;
use App\Http\Requests\StoreUpdateTaskRequest;
use App\Http\Requests\UpdateCompleteTaskRequest;
use App\Http\Requests\GetTaskRequest;

class TaskController extends Controller
{
    public function index(GetTaskRequest $request)
    {
        $queryParameters = $request->validated();
        $tasks = Task::query();
        if (array_key_exists('project', $queryParameters)) {
            if ($queryParameters['project'] === null) {
                $tasks->whereNull('project_id');
            } else {
                $tasks->where('project_id', $queryParameters['project']);
            }
        }
        if (array_key_exists('completed', $queryParameters)) {
            $tasks->where('completed', $queryParameters['completed']);
        }
        return TaskResource::collection($tasks->get());
    }

    public function show(Task $task)
    {
        return new TaskResource($task);
    }

    public function store(StoreUpdateTaskRequest $request)
    {
        $task = Task::create($request->validated());
        return new TaskResource($task);
    }

    public function update(StoreUpdateTaskRequest $request, Task $task)
    {
        $task->fill($request->validated());
        $task->save();
        return new TaskResource($task);
    }

    public function updateCompleted(UpdateCompleteTaskRequest $request, Task $task)
    {
        $task->completed = $request->validated()['completed'];
        $task->save();
        return new TaskResource($task);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(null, 204);
    }
}
