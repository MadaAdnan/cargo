<?php

namespace App\Filament\Employ\Widgets;

use App\Enums\ActivateAgencyEnum;
use App\Enums\BalanceTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\TaskAgencyEnum;
use App\Models\Agency;
use App\Models\Balance;
use Carbon\Carbon;
use Error;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class AgencyWidget extends BaseWidget
{
    protected static ?string $heading = "مهام إدارية";
protected static ?int $sort=5;
    /**
     * @return int|int[]|null[]|string
     */
    public function getColumnSpan(): array|int|string
    {
        return 2;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn() => Agency::where([
                    'agencies.activate' => ActivateAgencyEnum::PENDING->value,
                    'user_id' => auth()->id(),
                ])->orderBy('order_id')
            )
            ->columns([
//                Tables\Columns\TextColumn::make('task')->label('المهمة')->sortable(),
//                Tables\Columns\TextColumn::make('status')->label('نوع المهمة')->sortable(),
//                Tables\Columns\TextColumn::make('activate')->label('حالة المهمة')->sortable(),
                Tables\Columns\TextColumn::make('task')->label('ملاحظات'),

                Tables\Columns\TextColumn::make('order.status')->label('حالة الطلب')
                ,
                Tables\Columns\TextColumn::make('order.bay_type')->label('حالة الدفع')->searchable(),
                Tables\Columns\TextColumn::make('order.price')->label('التحصيل'),
                Tables\Columns\TextColumn::make('order.far')->label('أجور الشحن'),
                Tables\Columns\TextColumn::make('order.sender.name')->label('اسم المرسل')->searchable(),
                Tables\Columns\TextColumn::make('sender_phone')->label('هاتف المرسل')
                    ->url(fn($record) => $record->sender_phone
                        ? url('https://wa.me/' . ltrim($record->sender_phone, '+'))
                        : '#')
                    ->openUrlInNewTab()

                    ->searchable(),

                Tables\Columns\TextColumn::make('order.receive.name')->label('معرف المستلم ')->searchable(),
                Tables\Columns\TextColumn::make('order.receive.address')->label('عنوان المستلم ')->searchable(),
                Tables\Columns\TextColumn::make('receive_phone')->label('هاتف المستلم ')
                    ->url(fn($record) => url('https://wa.me/' . ltrim($record->receive_phone
                            ? url('https://wa.me/' . ltrim($record->receive_phone, '+'))
                            : '#')))->openUrlInNewTab()
                    ->searchable(),
                Tables\Columns\TextColumn::make('order.global_name')->label('اسم المستلم'),
                Tables\Columns\TextColumn::make('order.code')->label('كود الطلب')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('order.far_sender')->label('الاجور على المرسل ')->formatStateUsing(fn
                ($state) => $state ? '✅' : '❌') ->sortable() // استخدام الرموز التعبيرية
                ,
                Tables\Columns\TextColumn::make('order.created_at')->label('تاريخ الطلب')->sortable()
                    ->formatStateUsing(fn($state) => Carbon::parse($state)->diffForHumans()), // عرض الزمن بشكل نسبي



            ])
            ->actions([
                Tables\Actions\Action::make('complete_task')
                    ->form(function ($record) {

                        if ($record->status == TaskAgencyEnum::TAKE && $record->order->far > 0 && $record->order->far_sender == true) {
                            return [
                                Placeholder::make('place')->content('هل أنت متأكد من إنهاء المهمة وإستلام أجور الشحن ' . $record->order->far . ' $')
                            ];

                        }
                        return [Placeholder::make('place')->label('هل أنت متأكد من إنهاء المهمة')];


                    })
                    ->action(function ($record) {

                        \DB::beginTransaction();
                        try {
                            $record?->update([
                                'activate' => ActivateAgencyEnum::COMPLETE->value,
                            ]);
                            if ($record->status == TaskAgencyEnum::TAKE && $record->order->far > 0 && $record->order->far_sender == true) {
                                Balance::create([
                                    'type' => BalanceTypeEnum::CATCH->value,
                                    'credit' => 0,
                                    'debit' => $record->order->far,
                                    'info' => 'تحصيل أجور شحن الطلب #' . $record->id,
                                    'user_id' => $record->user->id,
                                    'total' => $record->user->total_balance - $record->far,
                                    'is_complete' => true,
                                   // 'order_id' => $record->order->id
                                ]);
                                $user = $record->order->sender;
                               /* if ($record->order->far_sender === false) {
                                    $user = $record->order->receive;
                                }*/

                                Balance::create([
                                    'type' => BalanceTypeEnum::PUSH->value,
                                    'credit' => $record->order->far,
                                    'debit' => 0,
                                    'info' => 'دفع أجور شحن الطلب #' . $record->order->id,
                                    'user_id' => $user->id,
                                    'total' => $user->total_balance + $record->order->far,
                                    'is_complete' => true,
                                   // 'order_id' => $record->order->id
                                ]);
                            }

                            \DB::commit();
                            Notification::make('success')->title('تمت العملية بنجاح')->success()->send();
                        } catch (\Exception | Error $e) {

                            DB::rollBack();
                            Notification::make('success')->title('فشلت العملية ')->body($e->getMessage() . '- ' . $e->getLine())->danger()->send();
                        }

                    })
                    ->requiresConfirmation()->label('إنهاء المهمة')
                    ->visible(fn($record) => $record->status != TaskAgencyEnum::DELIVER),
                //complete
                Tables\Actions\Action::make('complete_task_deliver')->label('إنهاء المهمة')
                    ->form(fn($record) => [
                        Placeholder::make('success')->label('تنبيه')->content(function ($record) {
                            if ($record->order->far_sender == false) {
                                return "أنت على وشك إنهاء الطلب و إستلام مبلغ {$record->order->total_price} $";
                            }
                            return "أنت على وشك إنهاء الطلب و إستلام مبلغ {$record->order->price} $";

                        })->extraAttributes(['style' => 'color:red'])
                    ])
                    ->action(function ($record) {
                        \DB::beginTransaction();
                        try {
                            $record->update([
                                'activate' => ActivateAgencyEnum::COMPLETE->value,
                            ]);
                            $record->order->update([
                                'status' => OrderStatusEnum::SUCCESS->value
                            ]);

                            $far=$record->order->far;
                            $price = $record->order->price;
                            $totalPrice=$price+$far;
                            if ($record->order->far_sender == true) {
                                $totalPrice = $price;
                            }
                            Balance::create([
                                'debit' => $totalPrice,
                                'credit' => 0,
                                'type' => BalanceTypeEnum::CATCH->value,
                                'info' => 'إستلام قيمة الطلب #' . $record->order->id,
                                'is_complete' => true,
                                'order_id' => $record->order->id,
                                'total' => $record->user->total_balance - $totalPrice,
                                'user_id' => $record->user->id,
                            ]);
                            Balance::create([
                                'debit' => 0,
                                'credit' => $totalPrice,
                                'type' => BalanceTypeEnum::PUSH->value,
                                'info' => 'دفع قيمة الطلب #' . $record->order->id,
                                'is_complete' => true,
                                'order_id' => $record->order->id,
                                'total' => $record->order->receive->total_balance + $totalPrice,
                                'user_id' => $record->order->receive->id,
                            ]);
                            Balance::where('order_id', $record->order->id)->update(['is_complete'=>true]);
                            \DB::commit();
                            Notification::make('success')->title('نجاح العملية')->success()->send();
                        } catch (\Exception | Error $e) {
                            \DB::rollBack();
                            Notification::make('success')->title('فشل العملية')->danger()->body($e->getMessage())->send();
                        }

                    })
                    ->visible(fn($record) => $record->status == TaskAgencyEnum::DELIVER)
            ]);
    }
}
