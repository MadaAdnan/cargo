<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BalanceResource extends JsonResource
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
            'credit'=>$this->credit,
            'debit'=>$this->debit,
            'info'=>$this->info,
            'customerName'=>$this->customer_name,
            'currency'=>$this->currency_id==1?'$':'₺',
            'pending'=>(bool)$this->pending,
            'createdAt'=>$this->created_at?->format('Y-m-d')
        ];
    }
}
