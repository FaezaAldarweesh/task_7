<?php

namespace App\Services;

use App\Models\Task;
use App\Events\TaskEvent;
use App\Models\Task_dependency;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Request;

class TaskService {
    //trait customize the methods for successful , failed , authentecation responses.
    use ApiResponseTrait;
    /**
     * method to view all tasks 
     * @param   Request $request
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function get_all_Tasks($type,$status,$assigned_to,$due_date,$priority,$depends_on){
        try {
            return Task::filter($type,$status,$assigned_to,$due_date,$priority,$depends_on)->get();
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
            $task->assigned_to = $data['assigned_to'] ?? null;

            if ($data['depends_on'] == null) {
                $task->status = 'Open';
                $task->depends_on = 0;
            } else {
                $task->depends_on = count($data['depends_on']);
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

           // event(new TaskEvent('Task', Auth::user()->name , 'create new task'));

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
                $task->depends_on = count($data['depends_on']);
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
            $task = Task::find($task_id)->load('Task_dependencies')->load('comments')->load('attachments');
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
            $task_dependancies = Task_dependency::where('task_id',$task_id)->get();

            $task->delete();
            foreach($task_dependancies as $task_dependancy){
                $task_dependancy->delete();
            }

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
            $task_dependancies = Task_dependency::withTrashed()->where('task_id',$task_id)->get();

            foreach($task_dependancies as $task_dependancy){
                $task_dependancy->restore();
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
            $task_dependancies = Task_dependency::onlyTrashed()->where('task_id',$task_id)->get();

            foreach($task_dependancies as $task_dependancy){
                $task_dependancy->forceDelete();
            }
            return $task->forceDelete();
        }catch (\Exception $e) { Log::error($e->getMessage()); return $this->failed_Response($e->getMessage(), 400);   
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with deleting task', 400);}
    }
//========================================================================================================================
    /**
     * method to 
     * @param   $task_id
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function update_status($data, $task_id)
    {
        try {
            // إيجاد المهمة المراد تحديث حالتها
            $task = Task::find($task_id);
            if (!$task) {
                throw new \Exception('Task not found');
            }
    
            // تحقق مما إذا كانت الحالة الجديدة هي نفسها الحالة القديمة
            if ($task->status === $data['status']) {
                return $task; // لا تقم بأي تحديث
            }
    
            // إذا كانت الحالة الجديدة هي "Completed"
            if ($data['status'] == 'Completed') {
                // تغيير حالة المهمة إلى "Completed"
                $task->status = 'Completed';
                $task->save();
    
                // إيجاد المهام التي تعتمد على هذه المهمة
                $dependent_tasks = Task_dependency::where('depends_id', '=', $task_id)->get();
    
                // تقليل عداد الاعتمادية وفتح المهام إذا انخفضت الاعتمادية إلى صفر
                foreach ($dependent_tasks as $dependent_task) {
                    $related_task = Task::where('id', '=', $dependent_task->task_id)->first();
                    if ($related_task) { // تحقق من وجود المهمة المعتمدة
                        $related_task->depends_on -= 1;
    
                        if ($related_task->depends_on == 0) {
                            $related_task->status = 'Open';
                        }
    
                        $related_task->save();
                    }
                }
    
            } elseif ($task->status == 'Completed' && $data['status'] == 'In progress') {
                // إذا كانت المهمة مكتملة وتمت إعادتها إلى "In progress"
                $task->status = 'In progress';
                $task->save();
    
                // جلب المهام التي تعتمد على هذه المهمة
                $dependent_tasks = Task_dependency::where('depends_id', '=', $task_id)->get();
    
                // زيادة عداد الاعتمادية وإعادة إغلاق المهام إذا كانت تعتمد على هذه المهمة
                foreach ($dependent_tasks as $dependent_task) {
                    $related_task = Task::where('id', '=', $dependent_task->task_id)->first();
                    if ($related_task) { // تحقق من وجود المهمة المعتمدة
                        $related_task->depends_on += 1;
    
                        if ($related_task->depends_on > 0) {
                            $related_task->status = 'Blocked';
                        }
    
                        $related_task->save();
                    }
                }
            } else {
                // لأي حالات أخرى (مثل In progress أو غيرها)
                $task->status = $data['status'];
                $task->save();
            }
    
            return $task;
    
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->failed_Response($e->getMessage(), 400);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return $this->failed_Response('Something went wrong with task status update', 400);
        }
    }
       
//========================================================================================================================
public function assign($data,$task_id) {
    try {
        $task = Task::find($task_id);
        if (!$task) {
            throw new \Exception('Task not found');
        }
        $task->assigned_to = $data['assigned_to'];
        $task->save();

        return $task;

    } catch (\Throwable $th) {
        Log::error($th->getMessage());
        return $this->failed_Response('Something went wrong with assign the task', 400);
    }
}
//========================================================================================================================
public function update_reassign($data,$task_id) {
    try {
        $task = Task::find($task_id);
        if (!$task) {
            throw new \Exception('Task not found');
        }
        $task->assigned_to = $data['assigned_to'] ?? $task->assigned_to;
        $task->save();

        return $task;

    } catch (\Throwable $th) {
        Log::error($th->getMessage());
        return $this->failed_Response('Something went wrong with reassign the task', 400);
    }
}
//========================================================================================================================
public function task_blocked(){
    try {
        return Task::where('status','=','Blocked')->get();
    } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with fetche tasks', 400);}
}
//========================================================================================================================
}
