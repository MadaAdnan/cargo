<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'level' => $this->level,
            'totalBalanceUsd' => $this->total_balance,
            'totalBalancePendingUsd' => $this->pending_balance,
            'totalBalanceTr' => $this->total_balance_tr,
            'totalBalancePendingTr' => $this->total_balance_tr_pending,
        ];
    }
}
