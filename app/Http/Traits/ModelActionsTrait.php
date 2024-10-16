<?php 

namespace App\Http\Traits;

use App\Models\Task;
use App\Models\TaskStatusUpdate;

trait ModelActionsTrait 
{            
    
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
    
}