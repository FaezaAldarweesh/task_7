<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ErrorTask extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'action',
        'model_name',
        'task_id',
        'coused_by',
        'descreption',
        'message',
    ];
  
    public function Task(){

        return $this->belongsTo(Task::class);
    }
}
