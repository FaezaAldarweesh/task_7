<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user id' => $this->id,
            'user name' => $this->name, 
            'user email' => $this->email, 
            'user role' => $this->role ?? 'Empolyee', 
        ];
    }
}
