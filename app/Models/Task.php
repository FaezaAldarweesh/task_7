<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Task extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'title',
        'created_by',
        'description',
        'type',
        'status',
        'priority',
        'due_date',
        'assigned_to',
        'depends_on',
    ];

    //Mutators due_date
    public function setPublishedAtAttribute($value)
    {
        $this->attributes['due_date'] = Carbon::parse($value)->format('Y-m-d H:i:s'); 
    }

//========================================================================================================================

     protected static function boot()
    {
        parent::boot();
        static::creating(function ($task) {
            $task->created_by = Auth::user()->id;
        });
    }

//========================================================================================================================

    public function scopeFilter(Builder $query, $type, $status, $assigned_to, $due_date, $priority, $depends_on)
    {
        // Filter on type 
        if (isset($type) && !empty($type)) {
            $query->where('type', '=', $type);
        }
    
        // Filter on status
        if (isset($status) && !empty($status)) {
            $query->where('status', '=', $status);
        }
    
        // Filter on assigned_to
        if (isset($assigned_to) && !empty($assigned_to)) {
            $query->where('assigned_to', '=', $assigned_to);
        }
    
        // Filter on due_date
        if (isset($due_date) && !empty($due_date)) {
            $query->where('due_date', '=', $due_date);
        }
    
        // Filter on priority
        if (isset($priority) && !empty($priority)) {
            $query->where('priority', '=', $priority);
        }
    
        // Filter on dependency
        if (isset($depends_on) && !empty($depends_on)) {
            $query->whereHas('Task_dependencies', function ($query) use ($depends_on) {
                $query->where('depends_id', '=', $depends_on);
            });
        }
    
        return $query;
    }  

//========================================================================================================================

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

    public function TaskStatusUpdates(){

        return $this->hasMany(TaskStatusUpdate::class);
    }

    public function ErrorTask(){

        return $this->hasMany(ErrorTask::class);
    }

}
