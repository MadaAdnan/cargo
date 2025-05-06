<?php

namespace App\Http\Resources\Api;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SenderOrRecevirOrderInfoResource extends JsonResource
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
            'name'=> $this->name,
            'email'=> $this->email,
            'phone'=> $this->phone,
            'address'=> $this->address,
            'city_id'=> $this->city_id,
            'city_name' => City::find($this->city_id)?->name ?? 'غير معروف',
        ];
    }
}
