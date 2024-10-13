<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task_dependency extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'task_id',
        'depends_id',
    ];

    public function tasks (){
        
        return $this->belongsToMany(Task::class);
    }
}
