<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'task id' => $this->id,
            'task title' => $this->title, 
            'task description' => $this->description, 
            'task type' => $this->type, 
            'task status' => $this->status,
            'task priority' => $this->priority, 
            'task due date' => $this->due_date, 
            'task assigned to' => $this->user->name ?? "have not assigned to employee yet", 
            'task dependencies on' => ($this->depends_on == 0) ? 'it does not have dependeny' : 'it have dependeny', 
        ];
    }
}
