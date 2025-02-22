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
        if ($order->pick_id == null) {
            $order->status = OrderStatusEnum::PENDING;
        } else {
            $order->status = OrderStatusEnum::PICK;
        }
        $order->created_by = auth()->id();
        $given_id = User::where([
            'level' => LevelUserEnum::BRANCH->value,
            'branch_id' => $order->branch_target_id
        ])->first()?->id;
        $order->given_id = $given_id;
        if ($given_id != null) {
            $order->status = OrderStatusEnum::TRANSFER;
        }


    }


    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {

        if ($order->receive_id != null) {
            \DB::beginTransaction();
            try {

                    HelperBalance::completePicker($order);



                \DB::commit();
            } catch (\Exception | \Error $e) {
                \DB::rollBack();
            }

        }

    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {


        if ($order->status->value == OrderStatusEnum::CANCELED->value /*|| $order->status->value == OrderStatusEnum::CONFIRM_RETURNED->value*/) {
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
