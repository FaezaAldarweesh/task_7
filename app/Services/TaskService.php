<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Comment;
use App\Models\ErrorTask;
use App\Models\Attachment;
use App\Models\Task_dependency;
use App\Models\TaskStatusUpdate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Traits\ModelActionsTrait;
use Illuminate\Support\Facades\Request;

class TaskService {
     /**
     * ApiResponseTrait: trait customize the methods for successful , failed , authentecation responses.
     * ModelActionsTrait: trait customize the method model to stor all life cycle of task and all Error messages.
     */
    use ApiResponseTrait,ModelActionsTrait;
    /**
     * method to view all tasks with a filtes on (type,status,assigned_to,due_date,priority,depends_on)
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
     * @param   $dtat
     * @return /Illuminate\Http\JsonResponse
     */
    public function create_Task($data) {
        try {

            //create new task
            $task = new Task();
            $task->title = $data['title'];
            $task->description = $data['description'];
            $task->type = $data['type'];
            $task->priority = $data['priority'];
            $task->due_date = $data['due_date'];
            $task->assigned_to = $data['assigned_to'] ?? null;

            //هنا ستتم معالجة حالة التاسك عند إضافته (إما أن يكون مفتوح للعمل أو مغلق بسبب اعتماده على تاسك سابق)
            //سنناقش في حال كانت مصفوفة التاسكات المعتمد عليها فارغة أو لا

            if ($data['depends_on'] == null) {
                //في حال كانت فارغة هذا يعني أنه لا يعتمد على تاسم قبله لبدأ العمل فيه و ستكون حالته مفتوح للعمل + مع الإشارة إلى أن عداد الاعتمادية سيكون صفر
                $task->status = 'Open';
                $task->depends_on = 0;
            //في حال أن مصفوفة الاعتمادية لم تكت فارغة
            } else {
                //سيتم تخزين عدد التاسكات المعتمد عليها في عداد الاعتمادية
                $task->depends_on = count($data['depends_on']);
                foreach ($data['depends_on'] as $depend) {
                    //من ثم سنقوم بالمشي على المصفوفة التاسكات المعتمد عليها و التحقق من حالتهم
                    $depend_id = $depend['id'];
                    $check_status_task = Task::select('id', 'status')->where('id', '=', $depend_id)->first();
                     //في حال إيجاد تاسك واحد فقط معتمد عليه حالته هي غير مكتمل
                    if ($check_status_task && $check_status_task->status != 'Completed') {
                        //سيتم إضافة حالة التاسك على أنه مغلق لوجود تاسك معتمد عليه حالته لا تزال غير مكتملة و من ثم
                        $task->status = 'Blocked';
                        break;
                    } else {
                        //و إلا في حال أنه صادف أي تاسك معتمد عليه حالته مكتمل ستكون حالة التاسك مفتوحة
                        $task->status = 'Open';
                    }
                }
            }

            $task->save();
            
            //تخزين التاسكات المعتمد عليها من خلال علاقة ال many to many
            if ($data['depends_on'] != null) {
                foreach ($data['depends_on'] as $depend) {
                    $depend_id = $depend['id'];
                    $task->Task_dependencies()->attach($depend_id);
                }
            }
            
            //إضافة سجل عملية تخزين تاسك إلى جدول TaskStatusUpdates
           $this->model('create','Task',$task->id, Auth::id(), $task);

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
     * @return /Illuminate\Http\JsonResponse
     */
    public function update_task($data,Task $task){
        try {  

            //في حال التعديل على معلومات تاسك منتهي يتم إعادة رسالة خطأ
            if($task->status == 'Completed'){

                $message = 'you can not update on task that Completed befor , you can only update task that still (Blocked , Open , In progress) , if you want to do that, update on status of the task first';
                
                //إضافة سجل خطأ إلى جدول ErrorTask
                $this->error('update','Task',$task->id, Auth::id(), $task ,$message);
                throw new \Exception($message);
            }

            //التعديل على معلومات التاسك في حال وجود تعديل عليها
            $task->title = $data['title'] ?? $task->title;
            $task->description = $data['description'] ?? $task->description;
            $task->type = $data['type'] ?? $task->type;
            $task->priority = $data['priority'] ?? $task->priority;
            $task->due_date = $data['due_date'] ?? $task->due_date;
            $task->assigned_to = $data['assigned_to'] ?? $task->assigned_to;

            //مناقشة حالة التاسك بالضبط مثل عملية الإضافة من الأجل التحقق من حالات التاسكات المعتمد عليها و من ثم الإقرار بحالة التاسك المعدل عليه
            if ($data['depends_on'] == null) {
                $task->status = 'Open';
                $task->depends_on = 0;
                if ($data['depends_on'] == null) { 
                    //هنا في حال تم التعديل على تاسك ليصبح غير معتمد على تاسك أخر ,, نقوم بفك الربط بينها في جدول ال pivot
                    $task->Task_dependencies()->detach();
                }
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

            //في حال التعديل على التاسكات المعتمد عليها 
            if ($data['depends_on'] != null) { 
                $depend_ids = collect($data['depends_on'])->pluck('id')->toArray(); 
                $task->Task_dependencies()->sync($depend_ids);
            }

           //إضافة سجل عملية تعديل على التاسك إلى جدول TaskStatusUpdates
           $this->model('Update','Task',$task->id, Auth::id(), $task);

            return $task;
            
        } catch (\Exception $e) { Log::error($e->getMessage()); return $this->failed_Response($e->getMessage(), 404);
        }catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with update task', 400);}
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
                //إضافة سجل خطأ إلى جدول ErrorTask
                $this->error('update','Task',null, Auth::id(), null ,'task not found');
                throw new \Exception('task not found');
            }else{
                $task->load('Task_dependencies', 'comments', 'attachments');
                return $task;
            }
        } catch (\Exception $e) { Log::error($e->getMessage()); return $this->failed_Response($e->getMessage(), 404);
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with view task', 400);}
    }
//========================================================================================================================
    /**
     * method to soft delete task alraedy exist
     * @param  $task_id
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function delete_task($task_id)
    {
        try {  
            $task = Task::find($task_id);
            if(!$task){
                //إضافة سجل خطأ إلى جدول ErrorTask
                $this->error('soft delte','Task',null, Auth::id(), null ,'task not found');
                throw new \Exception('task not found');
            }

            //حذف حميع السجلات المتعلقة بالتاسك في جميع الجداول
            Task_dependency::where('task_id', $task_id)->delete();

            Comment::where('commentable_id', $task_id)->delete();
            
            Attachment::where('attachmentable_id', $task_id)->delete();
            
            TaskStatusUpdate::where('task_id', $task_id)->delete();
            
            ErrorTask::where('task_id', $task_id)->delete();
            
            $task->delete();

            //تسجيل عملية الحذف المؤقت
           $this->model('soft delete','Task',$task_id, Auth::id(), null);

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
                //إضافة سجل خطأ إلى جدول ErrorTask
                $this->error('restore','Task',null, Auth::id(), null ,'task not found');
                throw new \Exception('task not found');
            }
            Task_dependency::where('task_id', $task_id)->restore();

            Comment::where('commentable_id', $task_id)->restore();
            
            Attachment::where('attachmentable_id', $task_id)->restore();
            
            TaskStatusUpdate::where('task_id', $task_id)->restore();
            
            ErrorTask::where('task_id', $task_id)->restore();

            //تسجيل عملية الاستعادة
            $this->model('restore','Task',$task_id, Auth::id(), null);

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
                //إضافة سجل خطأ إلى جدول ErrorTask
                $this->error('delete','Task',null, Auth::id(), null ,'task not found');
                throw new \Exception('task not found');
            }
            
            $task->forceDelete();
            
            return true;
        }catch (\Exception $e) { Log::error($e->getMessage()); return $this->failed_Response($e->getMessage(), 400);   
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with deleting task', 400);}
    }
//========================================================================================================================
    /**
     * method to update status
     * @param   $data
     * @param   $task_id
     * @return /Illuminate\Http\JsonResponse if have an error
     */
    public function update_status($data, $task_id)
    {
        try {
            $task = Task::find($task_id);
            if (!$task) {
                //إضافة سجل خطأ إلى جدول ErrorTask
                $this->error('update status', 'Task', null, Auth::id(), null, 'task not found');
                throw new \Exception('task not found');
            }
    
            // التحقق فيما إذا كانت الحالة القديمة تساوي الحالة الجديدة التي وصلت , في حال كان نفسها لا يتم التغير أبدا في حالات التاسكات المتعلقة به
            if ($task->status === $data['status']) {
                return $task;
            }
    
            // إذا كانت الحالة الجديدة هي "Completed"
            if ($data['status'] == 'Completed') {
                $task->status = 'Completed';
                $task->save();
    
                // إعادة المهام التي تعتمد على هذه المهمة
                $dependent_tasks = Task_dependency::where('depends_id', '=', $task_id)->get();
    
                // تقليل عداد الاعتمادية وفتح المهمة المتعلقة بالتاسك إذا انخفضت الاعتمادية إلى صفر
                foreach ($dependent_tasks as $dependent_task) {
                    $related_task = Task::where('id', '=', $dependent_task->task_id)->first();
                    if ($related_task) { // تحقق من وجود المهمة المعتمدة
                        $related_task->depends_on -= 1;
    
                        if ($related_task->depends_on == 0) {
                            $related_task->status = 'Open';
                        }
    
                        $related_task->save();
    
                        // تسجيل تحديث حالة المهمة المعتمدة
                        $this->model('Update status of related task', 'Task', $related_task->id, Auth::id(), $related_task);
                    }
                }
    
            //في حال كانت حالة التاسك هي مكتمل و تم إعادتها إلى حالة العمل
            } elseif ($task->status == 'Completed' && $data['status'] == 'In progress') {
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
    
                        // تسجيل تحديث حالة المهمة المعتمدة
                        $this->model('Update status of related task', 'Task', $related_task->id, Auth::id(), $related_task);
                    }
                }
            } else {
                // لأي حالات أخرى
                $task->status = $data['status'];
                $task->save();
            }
    
            // تسجيل تحديث حالة المهمة الأصلية
            $this->model('Update status', 'Task', $task->id, Auth::id(), $task);
    
            return $task;
    
        } catch (\Exception $e) { Log::error($e->getMessage()); return $this->failed_Response($e->getMessage(), 400);
        } catch (\Throwable $th) { Log::error($th->getMessage());  return $this->failed_Response('Something went wrong with task status update', 400);}
    } 
//========================================================================================================================
    /**
     * method to assign task to employee
     * @param   $data
     * @param   $task_id
     * @return /Illuminate\Http\JsonResponse
     */
    public function assign($data,$task_id) {
        try {
            $task = Task::find($task_id);
            if (!$task) {
                //إضافة سجل خطأ إلى جدول ErrorTask
                $this->error('update ststus','Task',null, Auth::id(), null ,'task not found');
                throw new \Exception('task not found');
            }
            $task->assigned_to = $data['assigned_to'];
            $task->save();

            $this->model('assign status','Task',$task->id, Auth::id(), $task);

            return $task;

        } catch (\Exception $e) { Log::error($e->getMessage()); return $this->failed_Response($e->getMessage(), 400);
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with assign the task', 400);}
    }
//========================================================================================================================
    /**
     * method to reassign task to employee
     * @param   $task_id
     * @param   $data
     * @return /Illuminate\Http\JsonResponse
     */
    public function update_reassign($data,$task_id) {
        try {
            $task = Task::find($task_id);
            if (!$task) {
                //إضافة سجل خطأ إلى جدول ErrorTask
                $this->error('update ststus','Task',null, Auth::id(), null ,'task not found');
                throw new \Exception('task not found');
            }
            $task->assigned_to = $data['assigned_to'] ?? $task->assigned_to;
            $task->save();

            $this->model('reassign status','Task',$task->id, Auth::id(), $task);

            return $task;

        } catch (\Exception $e) { Log::error($e->getMessage()); return $this->failed_Response($e->getMessage(), 400);
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with reassign the task', 400);}
    }
//========================================================================================================================
    /**
     * method to get all blocked tasks
     * @return /Illuminate\Http\JsonResponse
     */
    public function task_blocked(){
        try {
            return Task::where('status','=','Blocked')->get();
        } catch (\Throwable $th) { Log::error($th->getMessage()); return $this->failed_Response('Something went wrong with fetche tasks', 400);}
    }
//========================================================================================================================
}
