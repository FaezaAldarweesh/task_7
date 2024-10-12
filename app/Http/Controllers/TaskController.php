<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Services\TaskService;
use App\Http\Resources\TaskResources;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Requests\Store_Task_Request;
use App\Http\Requests\Update_Task_Request;

class TaskController extends Controller
{
    //trait customize the methods for successful , failed , authentecation responses.
    use ApiResponseTrait;
    protected $taskservices;
    /**
     * construct to inject Task Services 
     * @param TaskService $taskservices
     */
    public function __construct(TaskService $taskservices)
    {
        $this->taskservices = $taskservices;
    }
    //===========================================================================================================================
    /**
     * method to view all tasks
     * @param   Request $request
     * @return /Illuminate\Http\JsonResponse
     * TaskResources to customize the return responses.
     */
    public function index(Request $request)
    {  
        $tasks = $this->taskservices->get_all_Tasks();
        return $this->success_Response(TaskResources::collection($tasks), "All tasks fetched successfully", 200);
    }
    //===========================================================================================================================
    /**
     * method to store a new task
     * @param   Store_Task_Request $request
     * @return /Illuminate\Http\JsonResponse
     */
    public function store(Store_Task_Request $request)
    {
        $task = $this->taskservices->create_Task($request->validated());
        return $this->success_Response(new TaskResources($task), "Task created successfully.", 201);
    }
    
    //===========================================================================================================================
    /**
     * method to show task alraedy exist
     * @param  $task_id
     * @return /Illuminate\Http\JsonResponse
     */
    public function show($task_id)
    {
        $task = $this->taskservices->view_task($task_id);

        // In case error messages are returned from the services section 
        if ($task instanceof \Illuminate\Http\JsonResponse) {
            return $task;
        }
            return $this->success_Response(new TaskResources($task), "Task viewed successfully", 200);
    }
    //===========================================================================================================================
    /**
     * method to update task alraedy exist
     * @param  Update_Task_Request $request
     * @param  Task $task
     * @return /Illuminate\Http\JsonResponse
     */
    public function update(Update_Task_Request $request, Task $task)
    {
        $task = $this->taskservices->update_Task($request->validated(), $task);
        return $this->success_Response(new TaskResources($task), "Task updated successfully", 200);
    }
    //===========================================================================================================================
    /**
     * method to soft delete task alraedy exist
     * @param  $task_id
     * @return /Illuminate\Http\JsonResponse
     */
    public function destroy($task_id)
    {
        $task = $this->taskservices->delete_task($task_id);

        // In case error messages are returned from the services section 
        if ($task instanceof \Illuminate\Http\JsonResponse) {
            return $task;
        }
            return $this->success_Response(null, "task soft deleted successfully", 200);
    }
    //========================================================================================================================
    /**
     * method to return all soft delete tasks
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function all_trashed_task()
    {
        $tasks = $this->taskservices->all_trashed_task();
        return $this->success_Response(taskResources::collection($tasks), "All trashed tasks fetched successfully", 200);
    }
    //========================================================================================================================
    /**
     * method to restore soft delete task alraedy exist
     * @param   $task_id
     * @return /Illuminate\Http\JsonResponse
     */
    public function restore($task_id)
    {
        $delete = $this->taskservices->restore_task($task_id);

        // In case error messages are returned from the services section 
        if ($delete instanceof \Illuminate\Http\JsonResponse) {
            return $delete;
        }
            return $this->success_Response(null, "task restored successfully", 200);
    }
    //========================================================================================================================
    /**
     * method to force delete on task that soft deleted before
     * @param   $task_id
     * @return /Illuminate\Http\JsonResponse
     */
    public function forceDelete($task_id)
    {
        $delete = $this->taskservices->forceDelete_task($task_id);

        // In case error messages are returned from the services section 
        if ($delete instanceof \Illuminate\Http\JsonResponse) {
            return $delete;
        }
            return $this->success_Response(null, "task force deleted successfully", 200);
    }
        
    // //========================================================================================================================
}
