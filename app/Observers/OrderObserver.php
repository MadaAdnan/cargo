<?php

namespace App\Observers;

use App\Enums\BalanceTypeEnum;
use App\Enums\BayTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Helper\HelperBalance;
use App\Models\Balance;
use App\Models\Order;
use Filament\Notifications\Notification;
use App\Enums\LevelUserEnum;
use Illuminate\Foundation\Auth\User;

class OrderObserver
{

    public function creating(Order $order): void
    {
        if($order->pick_id==null){
            $order->status = OrderStatusEnum::PENDING;
        }else{
            $order->status = OrderStatusEnum::PICK;
        }

    }


    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {

        if ($order->pick_id != null) {
            \DB::beginTransaction();
            try {
                HelperBalance::completePicker($order);
                info('complete success order');
                \DB::commit();
            } catch (\Exception | \Error $e) {
                \DB::rollBack();
                info("Error Observe in created function");
                info('Message:'.$e->getMessage());
                info('File:'.$e->getFile(). ' Line:'.$e->getLine());
            }

        }

    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {



        if ($order->status->value == OrderStatusEnum::CANCELED->value || $order->status->value == OrderStatusEnum::RETURNED->value ) {
            $order->balances()->delete();
        }



    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        $order->balances()->delete();
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
