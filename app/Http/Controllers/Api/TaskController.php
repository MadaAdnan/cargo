<?php

namespace App\Http\Controllers\Api;

use App\Helper\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateTaskRequest;
use App\Http\Resources\Api\PaginateResource;
use App\Http\Resources\Api\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {
    //     $page = \request()->get('page') ?? 1;
    //     $tasks = Task::orWhere(['user_id' => auth()->id(), 'delegate_id' => auth()->id()])->latest()->paginate(15, ['*'], 'page', $page);;
    //     return ApiHelper::apiResponse([
    //         'tasks' => TaskResource::collection($tasks),
    //         'paginate' => new PaginateResource($tasks)
    //     ]);
    // }
    public function index(Request $request)
    {
        $page = $request->get('page') ?? 1;
        $isComplete = $request->get('is_complete');

        $query = Task::orWhere(['user_id' => auth()->id(), 'delegate_id' => auth()->id()])
        ->where('is_canceled' , false);
        if ($isComplete !== null) {
            $query->where('is_complete', (bool)$isComplete );
                //   ->where('is_canceled' , false);
        }

        $tasks = $query->latest()->paginate(15, ['*'], 'page', $page);

        return ApiHelper::apiResponse([
            'tasks' => TaskResource::collection($tasks),
            'paginate' => new PaginateResource($tasks)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateTaskRequest $request)
    {
        try {
            $data = $request->validated();
            $data['created_id'] = auth()->id();
            $task = Task::create($data);

            return ApiHelper::apiResponse([
                'task' => $task ,
            ], 200, 'success');

        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500 );
        }
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
        if($task->is_complete==true || $task->is_canceled==true){
            return ApiHelper::apiResponse([
                'msg'=>'لا يمكنك تاكيد مهمة ملغتاة او مكتملة',
            ],401,'error');
        }
        $task->update(['is_complete'=>true]);
        return ApiHelper::apiResponse([
            'task'=>new TaskResource($task)
        ]);
    }


    public function canceledTask(string $id , Request $request){

         // قواعد التحقق
            $validator = Validator::make($request->all(), [
                'cancel_info' => 'required|string'
            ], [
                'cancel_info.required' => 'حقل سبب الإلغاء مطلوب',
            ]);

            // إذا فشل التحقق
            if ($validator->fails()) {
                return ApiHelper::apiResponse([
                    'errors' => $validator->errors(),
                    'msg' => 'البيانات المدخلة غير صالحة'
                ], 422, 'error');
            }
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
        if($task->is_complete==true || $task->is_canceled==true){
            return ApiHelper::apiResponse([
                'msg'=>'لا يمكنك الغاء مهمة ملغاة او مكتملة',
            ],401,'error');
        }
        $task->update(['is_canceled'=>true , 'cancel_info' => $request->cancel_info]);
        return ApiHelper::apiResponse([
            'task'=>new TaskResource($task),
            'msg' => 'تم إلغاء المهمة بنجاح'
        ]);
    }
    public function incompleteCount()
    {
        $userId = auth()->id();

        $tasksCount = Task::where('is_complete', 0)
        ->where('is_canceled', false)
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhere('delegate_id', $userId);
            }) ->count();

        return ApiHelper::apiResponse([
            'incompleteTaskCount' => $tasksCount,
        ], 200, 'success');
    }



}



