<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvestmentResource extends JsonResource
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
            'amount' => $this->amount,
            'investment_type' => $this->investment_type,
            'expected_return' => $this->expected_return,
            'actual_return' => $this->actual_return,
            'status' => $this->status,
            'notes' => $this->notes,
            'invested_at' => $this->invested_at,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'investor' => new UserResource($this->whenLoaded('investor')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
