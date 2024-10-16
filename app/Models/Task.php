<?php

namespace App\Models;

use App\Events\TaskEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
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
        'depends_on',
    ];

    public function scopeFilter(Builder $query, $type, $status, $assigned_to, $due_date, $priority, $depends_on)
    {
        // Filter by type if provided
        if (isset($type) && !empty($type)) {
            $query->where('type', '=', $type);
        }
    
        // Filter by status if provided
        if (isset($status) && !empty($status)) {
            $query->where('status', '=', $status);
        }
    
        // Filter by assigned_to if provided
        if (isset($assigned_to) && !empty($assigned_to)) {
            $query->where('assigned_to', '=', $assigned_to);
        }
    
        // Filter by due_date if provided
        if (isset($due_date) && !empty($due_date)) {
            $query->where('due_date', '=', $due_date);
        }
    
        // Filter by priority if provided
        if (isset($priority) && !empty($priority)) {
            $query->where('priority', '=', $priority);
        }
    
        // Filter by dependency if provided
        if (isset($depends_on) && !empty($depends_on)) {
            $query->whereHas('Task_dependencies', function ($query) use ($depends_on) {
                $query->where('depends_id', '=', $depends_on);
            });
        }
    
        return $query;
    }    

    public function user(){

        return $this->belongsTo(User::class,'assigned_to','id');
    }

    public function Task_dependencies() {

        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_id');
    }

    public function comments (){
        
        return $this->morphMany(Comment::class,'commentable');
    }

    public function attachments (){
        
        return $this->morphMany(Attachment::class,'attachmentable');
    }


    // protected static function boot()
    // {
    //     parent::boot();

    //     static::created(function ($model) {
    //         // إطلاق الحدث عند إضافة نموذج جديد
    //     });

    //     static::updated(function ($model) {
    //         // إطلاق الحدث عند تحديث نموذج
    //         event(new TaskEvent('updated', $model));
    //     });

    //     static::deleted(function ($model) {
    //         // إطلاق الحدث عند حذف نموذج
    //         event(new TaskEvent('deleted', $model));
    //     });
    // }
}
