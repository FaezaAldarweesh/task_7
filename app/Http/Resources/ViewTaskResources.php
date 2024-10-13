<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ViewTaskResources extends JsonResource
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
            'task assigned to' => $this->assigned_to, 
            'task dependencies on' => ($this->depends_on == 0) ? 'it does not have dependeny' : 'it have dependeny', 
            'task dependency ' => TaskDependencyResource::collection($this->whenLoaded('Task_dependencies')),
        ];
    }
}
