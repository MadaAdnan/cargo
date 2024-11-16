<?php

namespace App\Observers;

use App\Models\City;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        if(empty($user->iban)){
            $temp = City::where('id', $user['city_id'])->pluck('branch_id')->first();

            $user->update([
                'iban' => "FC" . str_pad(random_int(0, 9999999999999999), 10, '0', STR_PAD_LEFT) . $user->id,
                'branch_id' => $temp
            ]);
            $user->save();
        }

    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {

            }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
//        $temp = City::where('id', $user['city_id'])->pluck('branch_id')->first();
//        $user->update([
//            'branch_id' => $temp
//        ]);
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
