<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskStatusUpdate extends Model
{
    use HasFactory;
    protected $fillable = [
        'action',
        'model_name',
        'task_id',
        'coused_by',
        'descreption',
    ];

    public function Task(){

        return $this->belongsTo(Task::class);
    }
}
