<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\Log;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Request;

class TaskService {
    //trait customize the methods for successful , failed , authentecation responses.
    use ApiResponseTrait;
    /**
     * method to view all tasks with filter on active
     * @param   Request $request
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function get_all_Tasks(){
        try {
            $task = Task::with('Task_dependencies')->get();
            return $task;
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with fetche tasks', 400);}
    }
//========================================================================================================================
    /**
     * method to store a new task
     * @param   $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function create_Task($data) {
        try {
            $task = new Task();
            $task->title = $data['title'];
            $task->description = $data['description'];
            $task->type = $data['type'];
            $task->priority = $data['priority'];
            $task->due_date = $data['due_date'];
            $task->assigned_to = $data['assigned_to'];

            if ($data['depends_on'] == null) {
                $task->status = 'Open';
                $task->depends_on = 0;
            } else {
                $task->depends_on = 1;
                foreach ($data['depends_on'] as $depend) {
                    $depend_id = $depend['id'];
                    $check_status_task = Task::select('id', 'status')->where('id', '=', $depend_id)->first();

                    if ($check_status_task && $check_status_task->status != 'Completed') {
                        $task->status = 'Blocked';
                        break;
                    } else {
                        $task->status = 'Open';
                    }
                }
            }

            $task->save();

            if ($data['depends_on'] != null) {
                foreach ($data['depends_on'] as $depend) {
                    $depend_id = $depend['id'];
                    $task->Task_dependencies()->attach($depend_id);
                }
            }

            return $task;

        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $this->failed_Response('Something went wrong with creating the task', 400);
        }
    }
//========================================================================================================================
    /**
     * method to update task alraedy exist
     * @param  $data
     * @param  Task $task
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function update_task($data,Task $task){
        try {  

            $task->title = $data['title'] ?? $task->title;
            $task->description = $data['description'] ?? $task->description;
            $task->type = $data['type'] ?? $task->type;
            $task->priority = $data['priority'] ?? $task->priority;
            $task->due_date = $data['due_date'] ?? $task->due_date;
            $task->assigned_to = $data['assigned_to'] ?? $task->assigned_to;

            if ($data['depends_on'] == null) {
                $task->status = 'Open';
                $task->depends_on = 0;
            } else {
                $task->depends_on = 1;
                foreach ($data['depends_on'] as $depend) {
                    $depend_id = $depend['id'];
                    $check_status_task = Task::select('id', 'status')->where('id', '=', $depend_id)->first();

                    if ($check_status_task && $check_status_task->status != 'Completed') {
                        $task->status = 'Blocked';
                        break;
                    } else {
                        $task->status = 'Open';
                    }
                }
            }

            $task->save();

            if ($data['depends_on'] != null) { 
                $depend_ids = collect($data['depends_on'])->pluck('id')->toArray(); 
                $task->Task_dependencies()->sync($depend_ids);
            }
            
            return $task;

        }catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with view task', 400);}
    }
//========================================================================================================================
    /**
     * method to show task alraedy exist
     * @param  $task_id
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function view_Task($task_id) {
        try {    
            $task = Task::find($task_id);
            if(!$task){
                throw new \Exception('task not found');
            }
            return $task;
        } catch (\Exception $e) { Log::error($e->getMessage()); return $this->failed_Response($e->getMessage(), 404);
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with update task', 400);}
    }
//========================================================================================================================
    /**
     * method to soft delete task alraedy exist
     * @param  Task $task
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function delete_task($task_id)
    {
        try {  
            $task = Task::find($task_id);
            if(!$task){
                throw new \Exception('task not found');
            }
            $task->delete();
            return true;
        }catch (\Exception $e) { Log::error($e->getMessage()); return $this->failed_Response($e->getMessage(), 400);
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with deleting task', 400);}
    }
//========================================================================================================================
    /**
     * method to return all soft delete tasks
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function all_trashed_task()
    {
        try {  
            $tasks = Task::onlyTrashed()->get();
            return $tasks;
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with view trashed task', 400);}
    }
//========================================================================================================================
    /**
     * method to restore soft delete task alraedy exist
     * @param   $task_id
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function restore_task($task_id)
    {
        try {
            $task = Task::withTrashed()->find($task_id);
            if(!$task){
                throw new \Exception('task not found');
            }
            return $task->restore();
        }catch (\Exception $e) { Log::error($e->getMessage()); return $this->failed_Response($e->getMessage(), 400);   
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with restore task', 400);
        }
    }
//========================================================================================================================
    /**
     * method to force delete on task that soft deleted before
     * @param   $task_id
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function forceDelete_task($task_id)
    {   
        try {
            $task = Task::onlyTrashed()->find($task_id);
            if(!$task){
                throw new \Exception('task not found');
            }
            return $task->forceDelete();
        }catch (\Exception $e) { Log::error($e->getMessage()); return $this->failed_Response($e->getMessage(), 400);   
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with deleting task', 400);}
    }
//========================================================================================================================
}
