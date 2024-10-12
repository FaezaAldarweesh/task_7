<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'title',
        'description',
        'type',
        'status',
        'priority',
        'due_date',
        'assigned_to',
    ];

    public function user(){

        return $this->belongsTo(User::class,'assigned_to','id');
    }

    public function Task_dependencies (){
        
        return $this->belongsToMany(Task_dependency::class);
    }
}
