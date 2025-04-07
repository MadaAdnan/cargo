<?php

namespace App\Http\Resources\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        /**
         * @var $this Order
         */
        $currentMarker=$this->currentUser;
        return [
            'id' => $this->id,
            'shippingDate' => $this->shipping_date,
            'createdBy' => $this->createdBy?->name,
            'isFarSender' => (bool)$this->far_sender,
            'unitName' => $this->unit?->name,
            'shippingFees' => (double)$this->far,
            'shippingFeesTr' => (double)$this->far_tr,
            'price' => (double)$this->price,
            'priceTr' => (double)$this->price_tr,
            'senderName'=>$this->sender?->full_name,
            'receiveName'=>$this->global_name,
            'receivePhone'=>$this->receive_phone,
            'citySource'=>$this->citySource?->name,
            'branchSource'=>$this->branchSource?->name,
            'cityTarget'=>$this->cityTarget?->name,
            'branchTarget'=>$this->branchTarget?->name,
            'status'=>$this->status,
            'qrCode'=>$this->qr_code,
            'msg'=>$this->canceled_info,
            'markers'=>MarkerResource::collection($this->markers),
            'currentMarker'=>$currentMarker!=null?new UserResource($currentMarker):null,
        ];
    }
}
