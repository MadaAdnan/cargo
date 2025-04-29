<?php

namespace App\Http\Resources\Api;
use App\Enums\OrderStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarkerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'userName'=>$this->user?->name,
            'userId'=>$this->user_id,
            'createdAt'=>$this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
