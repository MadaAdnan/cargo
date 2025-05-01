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
            'currency'=>$this->getCurrencySymbol(),
            // 'currency'=>$this->currency_id==1?'$':'₺',
            'pending'=>(bool)$this->pending,
            'createdAt'=>$this->created_at?->format('Y-m-d'),
            'total'=>$this->total,
        ];
    }

    protected function getCurrencySymbol(): string
{
    return match((string)$this->currency_id) {
        '1' => '$',       // دولار
        '2' => '₺',       // ليرة تركية
        '3' => 'ل.س',     // ليرة سورية
        default => '?',   // رمز افتراضي للقيم غير المعروفة
    };
}
}
