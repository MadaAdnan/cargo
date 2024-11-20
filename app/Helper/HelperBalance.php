<?php

namespace App\Helper;

use App\Enums\BalanceTypeEnum;
use App\Models\Balance;
use App\Models\Order;
use App\Models\User;


class HelperBalance
{

    public static function setPickOrder(Order $order)
    {
        /*  $sender = $order->sender;
          try {
              if ($order->far_sender == true) {
                  //
              }
          } catch (\Exception | \Error $e) {
              throw new \Exception($e->getMessage());
          }*/
    }

    public static function formatNumber($number)
    {
        // إذا كان الرقم يحتوي على كسور


        return doubleval(sprintf('%.3f', $number)); // يظهر 3 أرقام بعد الفاصلة العشرية

        // إذا كان الرقم صحيحا
        return (float)$number;
    }

    public static function completePicker(Order $order)
    {
        $sender = User::find($order->sender_id);
        $staff = User::find($order->pick_id);

        try {
            if ($order->far_sender == true) {
                if ($order->far > 0) {
                    Balance::create([
                        'credit' => $order->far,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $sender->id,
                        'info' => 'أجور شحن  #' . $order->code,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'currency_id' => 1,
                    ]);
                    Balance::create([
                        'credit' => 0,
                        'debit' => $order->far,
                        'order_id' => $order->id,
                        'user_id' => $sender->id,

                        'info' => 'دفع أجور شحن  #' . $order->code,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'currency_id' => 1,
                    ]);
                    Balance::create([
                        'credit' => $order->far,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $staff->id,

                        'info' => 'دفع أجور شحن  #' . $order->code,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'currency_id' => 1,
                    ]);
                }

                if ($order->far_tr > 0) {
                    Balance::create([
                        'credit' => $order->far_tr,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $sender->id,
                        'info' => 'أجور شحن  #' . $order->code,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'currency_id' => 2,
                    ]);
                    Balance::create([
                        'credit' => 0,
                        'debit' => $order->far_tr,
                        'order_id' => $order->id,
                        'user_id' => $sender->id,

                        'info' => 'دفع أجور شحن  #' . $order->code,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'currency_id' => 2,
                    ]);
                    Balance::create([
                        'credit' => $order->far_tr,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $staff->id,

                        'info' => 'دفع أجور شحن  #' . $order->code,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'currency_id' => 2,
                    ]);
                }


            }
            self:: pendingBalancePick($order);
        } catch (\Exception | \Error $e) {

            throw new \Exception($e->getMessage());
        }
    }


    public static function completeOrder(Order $order)
    {

        $sender = User::find($order->sender_id);
        $staff = User::find($order->given_id);
        $receive = User::find($order->receive_id);
        try {
            if ($order->far_sender == false) {
                if ($order->far > 0) {
                    Balance::create([
                        'credit' => $order->far,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $receive->id,
                        'currency_id' => 1,
                        'info' => 'أجور شحن  #' . $order->code,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                    ]);

                    Balance::create([
                        'credit' => 0,
                        'debit' => $order->far,
                        'order_id' => $order->id,
                        'user_id' => $receive->id,
                        'currency_id' => 1,
                        'info' => 'دفع أجور شحن  #' . $order->code,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                    ]);

                    Balance::create([
                        'credit' => $order->far,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $staff->id,
                        'currency_id' => 1,
                        'info' => 'دفع أجور شحن  #' . $order->code,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                    ]);
                }
//
                if ($order->far_tr > 0) {
                    Balance::create([
                        'credit' => $order->far_tr,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $receive->id,
                        'currency_id' => 2,
                        'info' => 'أجور شحن  #' . $order->code,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                    ]);

                    Balance::create([
                        'credit' => 0,
                        'debit' => $order->far_tr,
                        'order_id' => $order->id,
                        'user_id' => $receive->id,
                        'currency_id' => 2,
                        'info' => 'دفع أجور شحن  #' . $order->code,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                    ]);

                    Balance::create([
                        'credit' => $order->far_tr,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $staff->id,
                        'currency_id' => 2,
                        'info' => 'دفع أجور شحن  #' . $order->code,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                    ]);
                }
//
            }
            if ($order->price > 0) {
                Balance::create([
                    'credit' => $order->price,
                    'debit' => 0,
                    'order_id' => $order->id,
                    'user_id' => $receive->id,
                    'currency_id' => 1,
                    'info' => 'أجور تحصيل  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);

                Balance::create([
                    'credit' => 0,
                    'debit' => $order->price,
                    'order_id' => $order->id,
                    'user_id' => $receive->id,
                    'currency_id' => 1,
                    'info' => 'دفع أجور تحصيل  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);

                Balance::create([
                    'credit' => $order->price,
                    'debit' => 0,
                    'order_id' => $order->id,
                    'user_id' => $staff->id,
                    'currency_id' => 1,
                    'info' => 'دفع أجور تحصيل  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);

                Balance::create([
                    'credit' => 0,
                    'debit' => $order->price,
                    'order_id' => $order->id,
                    'user_id' => $sender->id,
                    'currency_id' => 1,
                    'info' => 'دفع أجور تحصيل  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);
            }
            if ($order->price_tr > 0) {
                Balance::create([
                    'credit' => $order->price_tr,
                    'debit' => 0,
                    'order_id' => $order->id,
                    'user_id' => $receive->id,
                    'currency_id' => 2,
                    'info' => 'أجور تحصيل  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);

                Balance::create([
                    'credit' => 0,
                    'debit' => $order->price_tr,
                    'order_id' => $order->id,
                    'user_id' => $receive->id,
                    'currency_id' => 2,
                    'info' => 'دفع أجور تحصيل  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);

                Balance::create([
                    'credit' => $order->price_tr,
                    'debit' => 0,
                    'order_id' => $order->id,
                    'user_id' => $staff->id,
                    'currency_id' => 2,
                    'info' => 'دفع أجور تحصيل  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);

                Balance::create([
                    'credit' => 0,
                    'debit' => $order->price_tr,
                    'order_id' => $order->id,
                    'user_id' => $sender->id,
                    'currency_id' => 2,
                    'info' => 'دفع أجور تحصيل  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                ]);
            }
            Balance::where('order_id', $order->id)->where('pending', true)->delete();
        } catch (\Exception | \Error $e) {
            throw new \Exception($e->getMessage());
        }


    }


    public static function pendingBalancePick(Order $order)
    {

        $sender = User::find($order->sender_id);
        $staff = User::find($order->pick_id);
        $receive = User::find($order->receive_id);


        try {
            if ($order->far_sender == false) {
                if ($order->far > 0) {
                    Balance::create([
                        'user_id' => $receive->id,
                        'debit' => 0,
                        'credit' => $order->far,
                        'info' => 'اجور شحن الطلب #' . $order->id,
                        'pending' => true,
                        'order_id' => $order->id,
                        'currency_id' => 1,
                    ]);
                }
                if ($order->far_tr > 0) {
                    Balance::create([
                        'user_id' => $receive->id,
                        'debit' => 0,
                        'credit' => $order->far_tr,
                        'info' => 'اجور شحن الطلب #' . $order->id,
                        'pending' => true,
                        'order_id' => $order->id,
                        'currency_id' => 2,
                    ]);
                }

            }

            if ($order->price > 0) {
                Balance::create([
                    'user_id' => $receive->id,
                    'debit' => 0,
                    'credit' => $order->price,
                    'info' => 'قيمة تحصيل الطلب #' . $order->id,
                    'pending' => true,
                    'currency_id' => 1,
                    'order_id' => $order->id
                ]);

                Balance::create([
                    'user_id' => $sender->id,
                    'debit' => $order->price,
                    'credit' => 0,
                    'info' => 'قيمة تحصيل الطلب #' . $order->id,
                    'pending' => true,
                    'currency_id' => 1,
                    'order_id' => $order->id
                ]);
            }

            if ($order->price_tr > 0) {
                Balance::create([
                    'user_id' => $receive->id,
                    'debit' => 0,
                    'credit' => $order->price_tr,
                    'info' => 'قيمة تحصيل الطلب #' . $order->id,
                    'pending' => true,
                    'currency_id' => 2,
                    'order_id' => $order->id
                ]);

                Balance::create([
                    'user_id' => $sender->id,
                    'debit' => $order->price_tr,
                    'credit' => 0,
                    'info' => 'قيمة تحصيل الطلب #' . $order->id,
                    'pending' => true,
                    'currency_id' => 2,
                    'order_id' => $order->id
                ]);
            }

        } catch (\Exception $e) {
            throw new \Exception('Error Pick Pending');
        }

    }

    public static function getMaxCodeAccount()
    {
        $user = User::withoutGlobalScope('userOnly')->where('is_account', true)->orderBy('iban', 'desc')->max('iban') ?? 1;
        return (int)$user + 1;
    }

    public static function confirmReturn(Order $order)
    {
        $customer = $order->sender;
        $staff = $order->returned;
        try{
            // add Far
            if ($order->far_sender == false) {
                if ($order->far > 0) {
                    Balance::create([
                        'credit' => $order->far,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $customer->id,
                        'currency_id' => 1,
                        'info' => 'أجور شحن  #' . $order->code,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                    ]);

                    Balance::create([
                        'credit' => 0,
                        'debit' => $order->far,
                        'order_id' => $order->id,
                        'user_id' => $customer->id,
                        'currency_id' => 1,
                        'info' => 'دفع أجور شحن  #' . $order->code,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                    ]);

                    Balance::create([
                        'credit' => $order->far,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $staff->id,
                        'currency_id' => 1,
                        'info' => 'دفع أجور شحن  #' . $order->code,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                    ]);
                }
//
                if ($order->far_tr > 0) {
                    Balance::create([
                        'credit' => $order->far_tr,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $customer->id,
                        'currency_id' => 2,
                        'info' => 'أجور شحن  #' . $order->code,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                    ]);

                    Balance::create([
                        'credit' => 0,
                        'debit' => $order->far_tr,
                        'order_id' => $order->id,
                        'user_id' => $customer->id,
                        'currency_id' => 2,
                        'info' => 'دفع أجور شحن  #' . $order->code,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                    ]);

                    Balance::create([
                        'credit' => $order->far_tr,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $staff->id,
                        'currency_id' => 2,
                        'info' => 'دفع أجور شحن  #' . $order->code,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                    ]);
                }
//
            }
            //Add Price
            if ($order->price > 0) {
                Balance::create([
                    'user_id' => $customer->id,
                    'debit' => 0,
                    'credit' => $order->price,
                    'info' => 'قيمة تحصيل الطلب #' . $order->id,
                    'pending' => true,
                    'currency_id' => 1,
                    'order_id' => $order->id
                ]);

                Balance::create([
                    'user_id' => $customer->id,
                    'debit' => $order->price,
                    'credit' => 0,
                    'info' => 'قيمة تحصيل الطلب #' . $order->id,
                    'pending' => true,
                    'currency_id' => 1,
                    'order_id' => $order->id
                ]);
            }

            if ($order->price_tr > 0) {
                Balance::create([
                    'user_id' => $customer->id,
                    'debit' => 0,
                    'credit' => $order->price_tr,
                    'info' => 'قيمة تحصيل الطلب #' . $order->id,
                    'pending' => true,
                    'currency_id' => 2,
                    'order_id' => $order->id
                ]);

                Balance::create([
                    'user_id' => $customer->id,
                    'debit' => $order->price_tr,
                    'credit' => 0,
                    'info' => 'قيمة تحصيل الطلب #' . $order->id,
                    'pending' => true,
                    'currency_id' => 2,
                    'order_id' => $order->id
                ]);
            }


        }catch (\Exception |\Error $e){
           throw new \Exception($e->getMessage());
        }
    }

}
