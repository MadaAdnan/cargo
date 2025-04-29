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

    protected $customData = [];

    public function __construct($resource, array $customData = [])
    {
        parent::__construct($resource);
        $this->customData = $customData;
    }

    public function toArray(Request $request): array
    {


           // استخدم القيمة المخصصة إذا كانت موجودة (حتى لو كانت 'مستلم غير معروف')
           if (isset($this->customData['display_name']) && $this->customData['display_name'] !== null)
            {
            $name = $this->customData['display_name'];
        } else {
            $name = $this->name;
        }
                // ضمان عدم وجود قيم فارغة
                // $name = $name ?? 'مستخدم غير معروف';


        return [
            'id' => $this->id,
            // 'name' => $this->name,
            'name' => $name,
            'email' => $this->email,
            'level' => $this->level,
            'totalBalanceUsd' => $this->total_balance,
            'totalBalancePendingUsd' => $this->pending_balance,
            'totalBalanceTr' => $this->total_balance_tr,
            'totalBalancePendingTr' => $this->total_balance_tr_pending,
        ];
    }
}
