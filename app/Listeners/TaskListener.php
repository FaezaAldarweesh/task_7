<?php

namespace App\Listeners;

use App\Models\Task;
use App\Events\TaskEvent;
use App\Models\TaskStatusUpdate;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaskListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

     /**
     * Handle the event.
     *
     * @param  \App\Events\TaskEvent  $event
     * @return void
     */
    public function handle(TaskEvent $event)
    {
        //dd($event);  
        // تسجيل المعلومات الخاصة بالعملية إذا كان الـ model صحيحًا
        TaskStatusUpdate::create([
            'model' => $event->model,
            'user' => $event->user->id,
            'log' => $event->log,
        ]);
    }
}
