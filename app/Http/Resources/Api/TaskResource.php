<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'sender'=>$this->from,
            'senderPhone'=>$this->sender_phone,
            'receive'=>$this->to,
            'receivePhone'=>$this->receive_phone,
            'body'=>$this->task,
            'is_complete'=>$this->is_complete,
            'is_canceled'=>$this->is_canceled,
            'cancel_info'=>$this->cancel_info,
            'createdAt'=>$this->created_at->format('Y-m-d H:i'),



        ];
    }
}
