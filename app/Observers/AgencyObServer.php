<?php

namespace App\Observers;

use App\Enums\BalanceTypeEnum;
use App\Enums\BayTypeEnum;
use App\Enums\TaskAgencyEnum;
use App\Models\Agency;
use App\Models\Balance;
use App\Models\Order;
use App\Models\User;

class AgencyObServer
{
    /**
     * Handle the Agency "created" event.
     */
    public function created(Agency $agency): void
    {
        /**
         * @var $user User
         * @var $order Order
         */



    }

    /**
     * Handle the Agency "updated" event.
     */
    public function updated(Agency $agency): void
    {
        //
    }

    /**
     * Handle the Agency "deleted" event.
     */
    public function deleted(Agency $agency): void
    {
        //
    }

    /**
     * Handle the Agency "restored" event.
     */
    public function restored(Agency $agency): void
    {
        //
    }

    /**
     * Handle the Agency "force deleted" event.
     */
    public function forceDeleted(Agency $agency): void
    {
        //
    }
}
