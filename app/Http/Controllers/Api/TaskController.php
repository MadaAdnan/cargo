<?php

namespace App\Http\Controllers\Api;

use App\Helper\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PaginateResource;
use App\Http\Resources\Api\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $page = \request()->get('page') ?? 1;
        $tasks = Task::orWhere(['user_id' => auth()->id(), 'delegate_id' => auth()->id()])->latest()->paginate(15, ['*'], 'page', $page);;
        return ApiHelper::apiResponse([
            'tasks' => TaskResource::collection($tasks),
            'paginate' => new PaginateResource($tasks)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $task = Task::where('created_id', auth()->id())->find($id);
        if ($task == null) {
            return ApiHelper::apiResponse([
                'msg'=>'لم يتم إيجاد المهمة',
            ],401,'error');
        }
        $task->update([
            'user_id' => $request->user_id,
            'delegate_id' => $request->delegate_id,
            'task' => $request->task,
            'from' => $request->sender,
            'to' => $request->receive,
            'receive_phone' => $request->phone,

        ]);
        $task->refresh();
        return ApiHelper::apiResponse([
            'task'=>new TaskResource($task)
        ]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function confirmedTask(string $id)
    {
        $task = Task::find($id);

        if ($task == null) {
            return ApiHelper::apiResponse([
                'msg'=>'لم يتم إيجاد المهمة',
            ],401,'error');
        }
        if($task->user_id!=auth()->id() && $task->delegate_id !=auth()->id()){
            return ApiHelper::apiResponse([
                'msg'=>'لم يتم إيجاد المهمة',
            ],401,'error');
        }
        $task->update(['is_complete'=>true]);
        return ApiHelper::apiResponse([
            'task'=>new TaskResource($task)
        ]);
    }
    public function incompleteCount()
    {
        $userId = auth()->id();

        $tasksCount = Task::where('is_complete', 0)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhere('delegate_id', $userId);
            }) ->count();

        return ApiHelper::apiResponse([
            'incompleteTaskCount' => $tasksCount,
        ], 200, 'success');
    }



}



