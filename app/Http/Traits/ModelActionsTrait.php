<?php 

namespace App\Http\Traits;

use App\Models\Task;
use App\Models\ErrorTask;
use App\Models\TaskStatusUpdate;

trait ModelActionsTrait 
{            
    /**
     * method to stor all life cycle of task.
     * @param  $action  // action type (create , update , delete , ......ect)
     * @param  $model_name //name name of model that action doing by it
     * @param  $task_id //id row task
     * @param  $coused_by //user who is doing this action
     * @param  $descreption //all respons of model as jeson
     */
    public function model($action,$model_name,$task_id,$coused_by,$descreption){
        TaskStatusUpdate::create([
            'action'=>$action,
            'model_name'=>$model_name,
            'task_id'=>$task_id,
            'coused_by' => $coused_by,
            'descreption' => $descreption,
        ]);
    }
    //========================================================================================================================
    /**
     * method to stor all error messages of task.
     * @param  $action  // action type (create , update , delete , ......ect)
     * @param  $model_name //name name of model that action doing by it
     * @param  $task_id //id row task
     * @param  $coused_by //user who is doing this action
     * @param  $descreption //all respons of model as jeson
     * @param  $message //error message
     */
    public function error($action,$model_name,$task_id,$coused_by,$descreption,$message){
        ErrorTask::create([
            'action'=>$action,
            'model_name'=>$model_name,
            'task_id'=>$task_id,
            'coused_by' => $coused_by,
            'descreption' => $descreption,
            'message' => $message
        ]);
    }
    //========================================================================================================================
    
}