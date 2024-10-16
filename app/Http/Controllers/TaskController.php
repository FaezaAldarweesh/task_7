<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Services\TaskService;
use App\Http\Resources\TaskResources;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Requests\Tasks_Requests\Store_Task_Request;
use App\Http\Resources\ViewTaskResources;
use App\Http\Requests\Tasks_Requests\Update_Task_Request;
use App\Http\Requests\Tasks_Requests\Update_Task_status_Request;
use App\Http\Requests\Tasks_Requests\assign_Request;
use App\Http\Requests\Tasks_Requests\update_reassign_Request;

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
                //security middleware
        $this->middleware('security');
        $this->taskservices = $taskservices;
    }
    //===========================================================================================================================
    /**
     * method to view all tasks with a filtes on (type,status,assigned_to,due_date,priority,depends_on)
     * @param   Request $request
     * @return /Illuminate\Http\JsonResponse
     * TaskResources to customize the return responses.
     */
    public function index(Request $request)
    {  
        $tasks = $this->taskservices->get_all_Tasks($request->input('type'),$request->input('status'),$request->input('assigned_to'),$request->input('due_date'),$request->input('priority'),$request->input('depends_on'));
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
            return $this->success_Response(new ViewTaskResources($task), "Task viewed successfully", 200);
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

         // In case error messages are returned from the services section 
         if ($task instanceof \Illuminate\Http\JsonResponse) {
            return $task;
        }
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
     * method to return all soft deleted tasks
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function all_trashed_tasks()
    {
        $tasks = $this->taskservices->all_trashed_task();
        return $this->success_Response(TaskResources::collection($tasks), "All trashed tasks fetched successfully", 200);
    }
    //========================================================================================================================
    /**
     * method to restore soft deleted task alraedy exist
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
        
    //========================================================================================================================
    /**
     * method to update status of task 
     * @param   Update_Task_status_Request $request
     * @param   $task_id
     * @return /Illuminate\Http\JsonResponse
     */
    public function update_status(Update_Task_status_Request $request,$task_id)
    {
        $update_status = $this->taskservices->update_status($request->validated(), $task_id);

        // In case error messages are returned from the services section 
        if ($update_status instanceof \Illuminate\Http\JsonResponse) {
            return $update_status;
        }
            return $this->success_Response( new TaskResources($update_status), "updated status task successfully", 200);
    }
        
    //========================================================================================================================
    /**
     * method to assign task to employee
     * @param   assign_Request $request
     * @param   $task_id
     * @return /Illuminate\Http\JsonResponse
     */
    public function assign(assign_Request $request,$task_id)
    {
        $update_status = $this->taskservices->assign($request->validated(), $task_id);

        // In case error messages are returned from the services section 
        if ($update_status instanceof \Illuminate\Http\JsonResponse) {
            return $update_status;
        }
            return $this->success_Response( new TaskResources($update_status), "assign task successfully", 200);
    }
        
    //========================================================================================================================
    /**
     * method to reassign task to employee
     * @param   $task_id
     * @param   update_reassign_Request $request
     * @return /Illuminate\Http\JsonResponse
     */
    public function update_reassign(update_reassign_Request $request,$task_id)
    {
        $update_status = $this->taskservices->update_reassign($request->validated(), $task_id);

        // In case error messages are returned from the services section 
        if ($update_status instanceof \Illuminate\Http\JsonResponse) {
            return $update_status;
        }
            return $this->success_Response( new TaskResources($update_status), "updated assign task successfully", 200);
    }
        
    //========================================================================================================================
    /**
     * method to get all blocked tasks
     * @return /Illuminate\Http\JsonResponse
     */
    public function task_blocked()
    {  
        $tasks = $this->taskservices->task_blocked();
        return $this->success_Response(TaskResources::collection($tasks), "All task blocked fetched successfully", 200);
    }
    //===========================================================================================================================
}
