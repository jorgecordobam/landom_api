<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'location' => $this->location,
            'budget' => $this->budget,
            'funding_goal' => $this->funding_goal,
            'current_funding' => $this->current_funding,
            'progress_percentage' => $this->progress_percentage,
            'status' => $this->status,
            'risk_level' => $this->risk_level,
            'category' => $this->category,
            'start_date' => $this->start_date,
            'expected_end_date' => $this->expected_end_date,
            'featured_image' => $this->featured_image,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'media' => ProjectMediaResource::collection($this->whenLoaded('media')),
            'investments' => InvestmentResource::collection($this->whenLoaded('investments')),
            'workers' => UserResource::collection($this->whenLoaded('workers')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
