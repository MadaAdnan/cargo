<?php

namespace App\Observers;

use App\Models\Package;

class PackageObServe
{
    /**
     * Handle the Package "created" event.
     */
    public function created(Package $package): void
    {
        $order=$package->order;
        if($order->total_weight==0 ||$order->total_weight==null ){
            $sum = $order->packages->sum('weight');
            $order->total_weight = $sum;
            $order->save();
        }
    }

    /**
     * Handle the Package "updated" event.
     */
    public function updated(Package $package): void
    {
        $order=$package->order;
        if($order->total_weight==0 ||$order->total_weight==null ){
            $sum = $order->packages->sum('weight');
            $order->total_weight = $sum;
            $order->save();
        }
    }

    /**
     * Handle the Package "deleted" event.
     */
    public function deleted(Package $package): void
    {
        $order=$package->order;
        if($order->total_weight==0 ||$order->total_weight==null ){
            $sum = $order->packages->sum('weight');
            $order->total_weight = $sum;
            $order->save();
        }
    }

    /**
     * Handle the Package "restored" event.
     */
    public function restored(Package $package): void
    {
        //
    }

    /**
     * Handle the Package "force deleted" event.
     */
    public function forceDeleted(Package $package): void
    {
        //
    }
}
