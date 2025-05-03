<?php

namespace App\Helper;

use App\Enums\BalanceTypeEnum;
use App\Models\Balance;
use App\Models\Order;
use App\Models\User;
use Str;


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
        $staff = User::find($order->receive_id);

        try {
            if ($order->far_sender == true) {
                if ($order->far > 0) {
                    Balance::create([
                        'credit' => $order->far,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $sender->id,
                        'info' => 'أجور شحن  #' . $order->id,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'currency_id' => 1,
                    'created_at'=>$order->created_at,
                    ]);
                  /*  Balance::create([
                        'credit' => 0,
                        'debit' => $order->far,
                        'order_id' => $order->id,
                        'user_id' => $sender->id,

                        'info' => 'دفع أجور شحن  #' . $order->id,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'currency_id' => 1,
                    ]);
                    Balance::create([
                        'credit' => $order->far,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $staff->id,

                        'info' => 'دفع أجور شحن  #' . $order->id,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'currency_id' => 1,
                    ]);*/
                }

                if ($order->far_tr > 0) {
                    Balance::create([
                        'credit' => $order->far_tr,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $sender->id,
                        'info' => 'أجور شحن  #' . $order->id,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'currency_id' => 2,
                        'created_at'=>$order->created_at,
                    ]);
                  /*  Balance::create([
                        'credit' => 0,
                        'debit' => $order->far_tr,
                        'order_id' => $order->id,
                        'user_id' => $sender->id,

                        'info' => 'دفع أجور شحن  #' . $order->id,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'currency_id' => 2,
                    ]);
                    Balance::create([
                        'credit' => $order->far_tr,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $staff->id,

                        'info' => 'دفع أجور شحن  #' . $order->id,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'currency_id' => 2,
                    ]);*/
                }


            }
            self:: pendingBalancePick($order);
        } catch (\Exception | \Error $e) {

            throw new \Exception($e->getMessage());
        }
    }
    public static function completePickerToRecive(Order $order)
    {
        $sender = User::find($order->sender_id);
        $staff = User::find($order->receive_id);

        try {
            if ($order->far_sender == true) {
                if ($order->far > 0) {
                    Balance::create([
                        'credit' => $order->far,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $sender->id,
                        'info' => 'أجور شحن  #' . $order->id,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'currency_id' => 1,
                        'created_at'=>$order->created_at,
                    ]);
                    Balance::create([
                        'credit' => 0,
                        'debit' => $order->far,
                        'order_id' => $order->id,
                        'user_id' => $sender->id,

                        'info' => 'دفع أجور شحن  #' . $order->id,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'currency_id' => 1,
                        'created_at'=>$order->created_at,
                    ]);
                    Balance::create([
                        'credit' => $order->far,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $staff->id,

                        'info' => 'دفع أجور شحن  #' . $order->id,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'currency_id' => 1,
                        'created_at'=>$order->created_at,
                    ]);
                }

                if ($order->far_tr > 0) {
                    Balance::create([
                        'credit' => $order->far_tr,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $sender->id,
                        'info' => 'أجور شحن  #' . $order->id,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'currency_id' => 2,
                        'created_at'=>$order->created_at,
                    ]);
                    Balance::create([
                        'credit' => 0,
                        'debit' => $order->far_tr,
                        'order_id' => $order->id,
                        'user_id' => $sender->id,

                        'info' => 'دفع أجور شحن  #' . $order->id,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'currency_id' => 2,
                        'created_at'=>$order->created_at,
                    ]);
                    Balance::create([
                        'credit' => $order->far_tr,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $staff->id,

                        'info' => 'دفع أجور شحن  #' . $order->id,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'currency_id' => 2,
                        'created_at'=>$order->created_at,
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
        $uuid = Str::uuid();
        $sender = User::find($order->sender_id);
        $staff = User::find($order->given_id);
        $receive = User::find($order->receive_id);

        $orderBalance = Balance::where('order_id', $order->id)->whereNotNull('color')->first();

        try {
            if ($order->far_sender == false) {
                if ($order->far > 0) {

                    Balance::create([
                        'uuid'=>$uuid,
                        'credit' => 0,
                        'debit' => $order->far,
                        'order_id' => $order->id,
                        'user_id' => $receive->id,
                        'currency_id' => 1,
                        'info' => 'دفع أجور شحن  #' . $order->id,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'created_at'=>$order->created_at,
                        'color' => $orderBalance?->color,
                        ]); // put 10 $ in system

                    Balance::create([
                        'uuid'=>$uuid,
                        'credit' => $order->far,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $receive->id,
                        'currency_id' => 1,
                        'info' => 'أجور شحن  #' . $order->id,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'created_at'=>$order->created_at,
                        'color' => $orderBalance?->color,
                    ]); // ظف الاسستلام سحب 10 السايقة لصالح مو




                    Balance::create([
                        'uuid'=>$uuid,
                        'credit' => $order->far,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $staff->id,
                        'currency_id' => 1,
                        'info' => 'دفع أجور شحن  #' . $order->id,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        // 'created_at'=>$order->created_at,
                        'color' => $orderBalance?->color,
                    ]); // استقبال 10 الاجور
                }
//
                if ($order->far_tr > 0) {
                    Balance::create([
                        'uuid'=>$uuid,
                        'credit' => $order->far_tr,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $receive->id,
                        'currency_id' => 2,
                        'info' => 'أجور شحن  #' . $order->id,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'created_at'=>$order->created_at,
                        'color' => $orderBalance?->color,
                    ]);

                    Balance::create([
                        'uuid'=>$uuid,
                        'credit' => 0,
                        'debit' => $order->far_tr,
                        'order_id' => $order->id,
                        'user_id' => $receive->id,
                        'currency_id' => 2,
                        'info' => 'دفع أجور شحن  #' . $order->id,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'created_at'=>$order->created_at,
                        'color' => $orderBalance?->color,
                    ]);

                    Balance::create([
                        'uuid'=>$uuid,
                        'credit' => $order->far_tr,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $staff->id,
                        'currency_id' => 2,
                        'info' => 'دفع أجور شحن  #' . $order->id,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        // 'created_at'=>$order->created_at,
                        'color' => $orderBalance?->color,
                    ]);
                }
//
            }
            if ($order->price > 0) {
                Balance::create([
                    'uuid'=>$uuid,
                    'credit' => 0,
                    'debit' => $order->price,
                    'order_id' => $order->id,
                    'user_id' => $sender->id,
                    'currency_id' => 1,
                    'info' => 'دفع أجور تحصيل  #' . $order->id,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                    'created_at'=>$order->created_at,
                    'color' => $orderBalance?->color,
                ]); // المرسل اودع 100 قيمة الشحنة عند النسلم

                Balance::create([
                    'uuid'=>$uuid,
                    'credit' => $order->price,
                    'debit' => 0,
                    'order_id' => $order->id,
                    'user_id' => $receive->id,
                    'currency_id' => 1,
                    'info' => 'أجور تحصيل  #' . $order->id,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                    'created_at'=>$order->created_at,
                    'color' => $orderBalance?->color,
                ]);//  المستلم عليه دين 100 للمرسل قيمة الشحنة


                Balance::create([
                    'uuid'=>$uuid,
                    'credit' => 0,
                    'debit' => $order->price,
                    'order_id' => $order->id,
                    'user_id' => $receive->id,
                    'currency_id' => 1,
                    'info' => 'دفع أجور تحصيل  #' . $order->id,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                    'created_at'=>$order->created_at,
                    'color' => $orderBalance?->color,
                ]); // المستبم دفع 100 يلي عليه دين لموظف التسليم



                Balance::create([
                    'uuid'=>$uuid,
                    'credit' => $order->price,
                    'debit' => 0,
                    'order_id' => $order->id,
                    'user_id' => $staff->id,
                    'currency_id' => 1,
                    'info' => 'دفع أجور تحصيل  #' . $order->id,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                    // 'created_at'=>$order->created_at,
                    'color' => $orderBalance?->color,
                ]); // موظف التسليم احد 100 من المستلم


            }
            if ($order->price_tr > 0) {
                Balance::create([
                    'uuid'=>$uuid,
                    'credit' => $order->price_tr,
                    'debit' => 0,
                    'order_id' => $order->id,
                    'user_id' => $receive->id,
                    'currency_id' => 2,
                    'info' => 'أجور تحصيل  #' . $order->id,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                    'created_at'=>$order->created_at,
                    'color' => $orderBalance?->color,
                ]);

                Balance::create([
                    'uuid'=>$uuid,
                    'credit' => 0,
                    'debit' => $order->price_tr,
                    'order_id' => $order->id,
                    'user_id' => $receive->id,
                    'currency_id' => 2,
                    'info' => 'دفع أجور تحصيل  #' . $order->id,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                    'created_at'=>$order->created_at,
                    'color' => $orderBalance?->color,
                ]);

                Balance::create([
                    'uuid'=>$uuid,
                    'credit' => $order->price_tr,
                    'debit' => 0,
                    'order_id' => $order->id,
                    'user_id' => $staff->id,
                    'currency_id' => 2,
                    'info' => 'دفع أجور تحصيل  #' . $order->id,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                    // 'created_at'=>$order->created_at,
                    'color' => $orderBalance?->color,
                ]);

                Balance::create([
                    'uuid'=>$uuid,
                    'credit' => 0,
                    'debit' => $order->price_tr,
                    'order_id' => $order->id,
                    'user_id' => $sender->id,
                    'currency_id' => 2,
                    'info' => 'دفع أجور تحصيل  #' . $order->id,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                    'created_at'=>$order->created_at,
                    'color' => $orderBalance?->color,
                ]);
            }
            Balance::where('order_id', $order->id)->where('pending', true)->delete();
        } catch (\Exception | \Error $e) {
            throw new \Exception($e->getMessage().'=='.$e->getLine());
        }


    }


    public static function pendingBalancePick(Order $order)
    {
        $uuid = Str::uuid();
        $sender = User::find($order->sender_id);
        $staff = User::find($order->pick_id);
        $receive = User::find($order->receive_id);


        try {
            if ($order->far_sender == false) {
                if ($order->far > 0) {
                    Balance::create([
                        'user_id' => $receive->id,
                        'debit' => 0,
                        'uuid'=>$uuid,
                        'credit' => $order->far,
                        'info' => 'اجور شحن الطلب #' . $order->id,
                        'pending' => true,
                        'order_id' => $order->id,
                        'currency_id' => 1,
                        'created_at'=>$order->created_at,
                    ]);
                }
                if ($order->far_tr > 0) {
                    Balance::create([
                        'uuid'=>$uuid,
                        'user_id' => $receive->id,
                        'debit' => 0,
                        'credit' => $order->far_tr,
                        'info' => 'اجور شحن الطلب #' . $order->id,
                        'pending' => true,
                        'order_id' => $order->id,
                        'currency_id' => 2,
                        'created_at'=>$order->created_at,
                    ]);
                }

            }

            if ($order->price > 0) {
                Balance::create([
                    'uuid'=>$uuid,
                    'user_id' => $receive->id,
                    'debit' => 0,
                    'credit' => $order->price,
                    'info' => 'قيمة تحصيل الطلب #' . $order->id,
                    'pending' => true,
                    'currency_id' => 1,
                    'order_id' => $order->id,
                    'created_at'=>$order->created_at,
                ]);

                Balance::create([
                    'uuid'=>$uuid,
                    'user_id' => $sender->id,
                    'debit' => $order->price,
                    'credit' => 0,
                    'info' => 'قيمة تحصيل الطلب #' . $order->id,
                    'pending' => true,
                    'currency_id' => 1,
                    'order_id' => $order->id,
                    'created_at'=>$order->created_at,
                ]);
            }

            if ($order->price_tr > 0) {
                Balance::create([
                    'uuid'=>$uuid,
                    'user_id' => $receive->id,
                    'debit' => 0,
                    'credit' => $order->price_tr,
                    'info' => 'قيمة تحصيل الطلب #' . $order->id,
                    'pending' => true,
                    'currency_id' => 2,
                    'order_id' => $order->id,
                    'created_at'=>$order->created_at,
                ]);

                Balance::create([
                    'uuid'=>$uuid,
                    'user_id' => $sender->id,
                    'debit' => $order->price_tr,
                    'credit' => 0,
                    'info' => 'قيمة تحصيل الطلب #' . $order->id,
                    'pending' => true,
                    'currency_id' => 2,
                    'order_id' => $order->id,
                    'created_at'=>$order->created_at,
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

    public static function confirmReturn(Order $order , $far =0 )
    {
        $customer = $order->sender;
        $staff = $order->returned;
        $orderBalance = Balance::where('order_id', $order->id)->whereNotNull('color')->first();

        // $existingBalanceColor = Balance::where('order_id', $order->id)->where('color', 'green')->first();
        try {
            // add Far
            if ($order->far_sender == false) {
                if ($order->far > 0  && $far == 1) {
                    Balance::create([
                        'credit' => $order->far,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $customer->id,
                        'currency_id' => 1,
                        'info' => 'أجور شحن  #' . $order->id,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'created_at'=>$order->created_at,
                        // 'color'=> $existingBalanceColor? 'green' : null,
                        'color' => $orderBalance?->color,
                    ]);
                }
//
                if ($order->far_tr > 0  && $far == 1) {
                    Balance::create([
                        'credit' => $order->far_tr,
                        'debit' => 0,
                        'order_id' => $order->id,
                        'user_id' => $customer->id,
                        'currency_id' => 2,
                        'info' => 'أجور شحن  #' . $order->id,
                        'type' => BalanceTypeEnum::CATCH->value,
                        'is_complete' => true,
                        'created_at'=>$order->created_at,
                        'color' => $orderBalance?->color,
                    ]);
                }
//
            }
            //Add Price
            if ($order->price > 0) {
                Balance::create([
                    'credit' => $order->price,
                    'debit' => 0,
                    'order_id' => $order->id,
                    'user_id' => $customer->id,
                    'currency_id' => 1,
                    'info' => 'أجور تحصيل  #' . $order->id,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                    'created_at'=>$order->created_at,
                    'color' => $orderBalance?->color,
                ]);

              Balance::create([
                    'credit' => 0,
                    'debit' => $order->price,
                    'order_id' => $order->id,
                    'user_id' => $customer->id,
                    'currency_id' => 1,
                    'info' => 'دفع أجور تحصيل  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                  'created_at'=>$order->created_at,
                  'color' => $orderBalance?->color,
                ]);
            }
            if ($order->price_tr > 0) {
                Balance::create([
                    'credit' => $order->price_tr,
                    'debit' => 0,
                    'order_id' => $order->id,
                    'user_id' => $customer->id,
                    'currency_id' => 2,
                    'info' => 'أجور تحصيل  #' . $order->id,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                    'created_at'=>$order->created_at,
                    'color' => $orderBalance?->color,
                ]);

               Balance::create([
                    'credit' => 0,
                    'debit' => $order->price_tr,
                    'order_id' => $order->id,
                    'user_id' => $customer->id,
                    'currency_id' => 2,
                    'info' => 'دفع أجور تحصيل  #' . $order->code,
                    'type' => BalanceTypeEnum::CATCH->value,
                    'is_complete' => true,
                   'created_at'=>$order->created_at,
                   'color' => $orderBalance?->color,
                ]);
            }
            Balance::where('order_id', $order->id)->where('pending', true)->delete();

        } catch (\Exception | \Error $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function getBalanceTypeArray(){
        return [
            'ارساليات'=>'ارساليات',
            'محروقات'=>'محروقات',
            'اصلاح الية '=>'اصلاح الية ',
            'آجارات وعقارات'=>'آجارات وعقارات',
            'انترنت واتصال'=>'انترنت واتصال',
            'مصاريف مكتب'=>'مصاريف مكتب',

        ];
    }

    public static function cancelOrder(Order $order)
    {
        $sender = User::find($order->sender_id);
        // $staff = User::find($order->given_id);
        // $receive = User::find($order->receive_id);

         try {
            Balance::create([
                'credit' => 0,
                'debit' => 0,
                'order_id' => $order->id,
                'user_id' => $sender->id,
                'currency_id' => 1,
                'info' => 'شحنة ملغاة #' . $order->id,
                'type' => BalanceTypeEnum::CATCH->value,
                'is_complete' => true,
                'created_at'=>$order->created_at,
                ]);
            Balance::where('order_id', $order->id)->where('pending', true)->delete();
        } catch (\Exception | \Error $e) {
            throw new \Exception($e->getMessage().'=='.$e->getLine());
        }


    }

}
