<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task_dependency extends Model
{
    use HasFactory;
    protected $fillable = [
        'task_id',
        'depends_id',
    ];

    public function tasks (){
        
        return $this->belongsToMany(Task::class);
    }
}
