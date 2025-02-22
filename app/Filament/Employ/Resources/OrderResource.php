<?php

namespace App\Filament\Employ\Resources;

use App\Enums\ActivateAgencyEnum;
use App\Enums\ActivateStatusEnum;
use App\Enums\BalanceTypeEnum;
use App\Enums\BayTypeEnum;
use App\Enums\FarType;
use App\Enums\LevelUserEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\TaskAgencyEnum;
use App\Filament\Employ\Resources\OrderResource\Pages;
use App\Filament\Employ\Resources\OrderResource\RelationManagers;
use App\Helper\HelperBalance;
use App\Models\Agency;
use App\Models\Balance;
use App\Models\Branch;
use App\Models\City;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Error;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use LaraZeus\Popover\Tables\PopoverColumn;


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $pluralModelLabel = 'الشحنات';

    protected static ?string $label = 'شحنة';
    protected static ?string $navigationLabel = 'الشحنات';

    public static function canCreate(): bool
    {
        return false;//parent::canCreate(); // TODO: Change the autogenerated stub
    }

    public static function canEdit(Model $record): bool
    {
        return parent::canEdit($record) && $record->status === OrderStatusEnum::PENDING; // TODO: Change the autogenerated stub
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('الطلبات')->schema([
                    Forms\Components\Fieldset::make('معلومات المرسل')
                        ->schema([
                            Forms\Components\Grid::make()->schema([
                                Forms\Components\Select::make('type')->options([
                                    OrderTypeEnum::HOME->value => OrderTypeEnum::HOME->getLabel(),
                                ])->label('نوع الطلب')->searchable()
                                    ->default(OrderTypeEnum::BRANCH->getLabel()),

                                Forms\Components\Select::make('sender_id')->relationship('sender', 'name',fn($query)=>$query->active())->label('معرف المرسل')
                                    ->afterStateUpdated(function ($state, $set) {
                                        $user = User::find($state);
                                        if ($user) {
                                            $set('sender_phone', $user->phone);
                                            $set('sender_address', $user->address);
                                            $set('city_source_id', $user?->city_id);

                                        }
                                    })->live()->searchable()->preload(),
                            ]),
                            Forms\Components\Grid::make()->schema([
                                Forms\Components\TextInput::make('general_sender_name')->label('اسم المرسل'),


                                Forms\Components\Select::make('branch_source_id')->relationship('branchSource', 'name')->label('اسم الفرع المرسل')
                                    ->afterStateUpdated(function ($state, $set) {
                                        $branch = Branch::find($state);
                                        if ($branch) {
                                            $set('city_source_id', $branch->city_id);
                                        }
                                    })->live()->required()->searchable()->preload(),
                            ]),
                            Forms\Components\Grid::make()->schema([
                                Forms\Components\TextInput::make('sender_phone')->label('رقم هاتف المرسل'),
                                Forms\Components\TextInput::make('sender_address')->label('عنوان المرسل'),
                            ]),
                            Forms\Components\Grid::make()->schema([
                                Forms\Components\Select::make('city_source_id')->relationship('citySource', 'name')
                                    ->label('من بلدة')->searchable()->preload(),
                            ]),
                        ]),

                    Forms\Components\Fieldset::make('معلومات المستلم')->schema([
                        Forms\Components\Grid::make()->schema([
                            Forms\Components\Select::make('receive_id')->relationship('receive', 'name',fn($query)=>$query->active())->label('معرف المستلم')->searchable()->preload()
                                ->afterStateUpdated(function ($state, $set) {
                                    $user = User::find($state);
                                    if ($user) {
                                        $set('receive_phone', $user->phone);
                                        $set('receive_address', $user->address);
                                        $set('city_target_id', $user?->city_id);


                                    }
                                })->live(),


                        ]),
                        Forms\Components\Grid::make()->schema([
                            Forms\Components\TextInput::make('receive_phone')->label('هاتف المستلم'),
                            Forms\Components\TextInput::make('receive_address')->label('عنوان المستلم'),
                        ]),
                        Forms\Components\Grid::make()->schema([
                            Forms\Components\TextInput::make('global_name')->label('اسم المستلم'),


                            Forms\Components\Select::make('city_target_id')->relationship('cityTarget', 'name')
                                ->label('الى بلدة')->searchable()->preload(),
                        ]),

                    ]),


                    Forms\Components\Fieldset::make('معلومات الطلب')->schema([
                        Forms\Components\Grid::make(3)->schema([

                            Forms\Components\Select::make('size_id')
                                ->relationship('size', 'name')
                                ->label
                                ('فئة الحجم'),


                            Forms\Components\Select::make('unit_id')->relationship('unit', 'name')->label('الوحدة')->required(),

                            Forms\Components\Select::make('weight_id')
                                ->relationship('weight', 'name')
                                ->label
                                ('فئة الوزن')->searchable()->preload(),
                            Forms\Components\Grid::make(1)->schema([
                                Forms\Components\DatePicker::make('shipping_date')->required()->label('تاريخ الشحنة'),
                            ]),
                            Forms\Components\TextInput::make('note')->label('ملاحظات')
                        ]),


                    ]),

                    Forms\Components\Fieldset::make('الأجور')->schema([
                        Forms\Components\Grid::make()->schema([
                            Forms\Components\TextInput::make('price')->numeric()->label('التحصيل')->default(0),
                            Forms\Components\TextInput::make('far')->numeric()->label('أجور الشحن')->default(1),

                            Forms\Components\Radio::make('far_sender')
                                ->options([
                                    true => 'المرسل',
                                    false => 'المستلم'
                                ])->required()->default(false)->inline()
                                ->label('أجور الشحن')->default(1),
                        ]),
                    ])
                ]),
                Forms\Components\Section::make('محتويات الطلب')
                    ->schema([
                        Forms\Components\Repeater::make('packages')->relationship('packages')->schema([
                            SpatieMediaLibraryFileUpload::make('package')->label('صورة الشحنة')->collection('packages'),


                            Forms\Components\TextInput::make('info')->label('معلومات الشحنة'),

                            Forms\Components\TextInput::make('quantity')->numeric()->label('الكمية'),


                        ])
                            ->collapsible()
                            ->collapsed(),
                    ])->collapsible()->collapsed(true)->visible(false),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
          //  ->poll(10)
            ->columns([
                PopoverColumn::make('qr_url')
                    ->trigger('click')
                    ->placement('right')
                    ->content(fn($record) => \LaraZeus\Qr\Facades\Qr::render($record->code))
                    ->icon('heroicon-o-qr-code'),

                Tables\Columns\TextColumn::make('code')->description(fn($record) => $record->id, 'above')->copyable()->searchable(),


                Tables\Columns\TextColumn::make('type')->label('نوع الطلب')
                    ->description(fn($record) => $record->status?->getLabel())
                    ->extraCellAttributes(function ($record) {
                        $list = [];
                        switch ($record->status) {
                            case OrderStatusEnum::PICK:
                                $list = ['style' => 'background-color:yellow'];
                                break;
                            case OrderStatusEnum::TRANSFER:
                                $list = ['style' => 'background-color:orange'];
                                break;
                            case OrderStatusEnum::RETURNED:
                                $list = ['style' => 'background-color:red'];
                                break;
                            case OrderStatusEnum::CANCELED:
                                $list = ['style' => 'background-color:gray;color:black'];
                                break;
                            case OrderStatusEnum::SUCCESS:
                                $list = ['style' => 'background-color:green;'];
                                break;
                        }
                        return $list;
                    }),
                Tables\Columns\TextColumn::make('far_sender')->formatStateUsing(fn($state) => FarType::tryFrom($state)?->getLabel())
                    ->color(fn($state) => FarType::tryFrom($state)?->getColor())
                    ->icon(fn($state) => FarType::tryFrom($state)?->getIcon())
                    ->label('حالة الدفع')
                    ->description(fn($record) => $record->created_at->diffForHumans())
                    ->searchable(),

                Tables\Columns\TextColumn::make('unit.name')->label('نوع الشحنة'),

                Tables\Columns\TextColumn::make('price')->formatStateUsing(fn($state) => $state . ' $ ')->label('التحصيل USD')->description(fn($record) => 'اجور الشحن : ' . $record->far . ' $ '),
                Tables\Columns\TextColumn::make('price_tr')->formatStateUsing(fn($state) => $state . 'TRY')->label('التحصيل TRY')->description(fn($record) => 'اجور الشحن : ' . $record->far_tr . 'TRY'),

                Tables\Columns\TextColumn::make('currency.name')->label('العملة'),

                Tables\Columns\TextColumn::make('sender.name')->label('اسم المرسل')->description(fn($record) => $record->general_sender_name)->searchable(),

                Tables\Columns\TextColumn::make('citySource.name')->label('من بلدة')->description(fn($record) => "إلى {$record->cityTarget?->name}")->searchable(),
                Tables\Columns\TextColumn::make('receive.name')->label('معرف المستلم ')->description(fn($record) => $record->global_name)->searchable(),
                Tables\Columns\TextColumn::make('receive_address')->label('هاتف المستلم ')->description(fn($record) => $record->receive_phone)
                    ->url(fn($record) => url('https://wa.me/' . ltrim($record?->receive_phone, '+')))->openUrlInNewTab()
                    ->searchable(),

                Tables\Columns\TextColumn::make('note')->label('ملاحظات')->color('primary'),
              Tables\Columns\TextColumn::make('shipping_date')->date('y-m-d')->label('تاريخ الشحنة'),
              Tables\Columns\TextColumn::make('created_at')->date('y-m-d')->label('تاريخ إنشاء الشحنة'),

          ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('success_pick')
                    ->form(function ($record) {
                        $farMessage = 'انت على وشك تأكيد إستلام مبلغ : ';
                        if ($record->far_sender == true && ($record->far > 0 || $record->far_tr > 0)) {

                            if ($record->far_tr > 0) {

                                $farMessage .= $record->far_tr . ' TRY ';
                            }
                            if ($record->far > 0) {
                                $farMessage .= ' و' . $record->far . ' USD ';
                            }
                            $farMessage .= 'أجور شحن';
                            return [
                                Forms\Components\Placeholder::make('msg')->content($farMessage)->extraAttributes(['style' => 'color:red;font-weight:900;font-size:1rem;'])->label('تنبيه')
                            ];
                        }
                        return [
                            Forms\Components\Placeholder::make('msg')->content("أنت على وشك تأكيد إلتقاط الطلب ")->extraAttributes(['style' => 'color:red;font-weight:900;font-size:1rem;'])->label('تنبيه')
                        ];
                    })
                    ->action(function ($record, $data) {
                        DB::beginTransaction();
                        try {
                            HelperBalance::completePicker($record);
                            $record->update(['status' => OrderStatusEnum::PICK->value]);
                            DB::commit();
                            Notification::make('success')->title('نجاح العملية')->body('تم تأكيد إلتقاط الطلب')->success()->send();
                        } catch (\Exception | Error $e) {
                            Notification::make('error')->title('فشل العملية')->body($e->getLine())->danger()->send();
                        }
                    })
                    ->label('تأكيد إلتقاط الشحنة')->button()->color('info')
                    ->visible(fn($record) => $record->pick_id == auth()->id() && $record->status == OrderStatusEnum::AGREE),

                Tables\Actions\Action::make('success_given')
                    ->form(function ($record) {

                        $totalPrice = (double)$record->price + (double)$record->far;
                        if ($totalPrice == 0) {
                            $totalPrice = (double)$record->price_tr + (double)$record->far_tr;
                        }
                        $priceMessage = 'انت تأكد إستلامك مبلغ : ';


                        if ($record->price_tr > 0) {
                            $priceMessage .= $record->price_tr . ' TRY ';
                        }
                        if ($record->price > 0) {
                            $priceMessage .= ' و ' . $record->price . ' USD ';
                        }
                        $priceMessage .= 'قيمة تحصيل الطلب';


                        $farMessage = '';

                        if ($record->far_sender == false) {
                            $farMessage = 'انت تأكد إستلامك مبلغ : ';
                            if ($record->far_tr > 0) {
                                $farMessage .= $record->far_tr . ' TRY ';
                            }
                            if ($record->far > 0) {
                                $farMessage .= ' و ' . $record->far . ' USD ';
                            }
                            $farMessage .= 'أجور شحن الطلب';

                        }

                        if ($totalPrice > 0) {
                            $form = [
                                Forms\Components\Placeholder::make('msg')->content($priceMessage)->extraAttributes(['style' => 'color:red;font-weight:900;font-size:1rem;'])->label('تنبيه'),
                                Forms\Components\Placeholder::make('msg_2')->content($farMessage)->extraAttributes(['style' => 'color:red;font-weight:900;font-size:1rem;'])->label('تنبيه')
                            ];
                        } else {
                            $form = [
                                Forms\Components\Placeholder::make('msg')->content("أنت على وشك تأكيد تسليم الطلب ")->extraAttributes(['style' => 'color:red;font-weight:900;font-size:1rem;'])->label('تنبيه')
                            ];
                        }
                        return $form;

                    })
                    ->action(function ($record, $data) {
                        DB::beginTransaction();
                        try {
                            HelperBalance::completeOrder($record);
                            $record->update(['status' => OrderStatusEnum::SUCCESS->value]);
                            DB::commit();
                            Notification::make('success')->title('نجاح العملية')->body('تم تأكيد تسليم الطلب')->success()->send();
                        } catch (\Exception | Error $e) {
                            Notification::make('error')->title('فشل العملية')->body($e->getLine())->danger()->send();
                        }
                    })->label('تأكيد تسليم الشحنة')->button()->color('info')
                    ->visible(fn($record) => $record->given_id == auth()->id() && ($record->status == OrderStatusEnum::TRANSFER || $record->status == OrderStatusEnum::PICK)),

                Tables\Actions\Action::make('cancel_order')
                    ->form([
                        Forms\Components\Radio::make('status')->options([
                            //OrderStatusEnum::CANCELED->value => OrderStatusEnum::CANCELED->getLabel(),
                            OrderStatusEnum::RETURNED->value => OrderStatusEnum::RETURNED->getLabel(),
                        ])->label('الحالة')->required()->default(OrderStatusEnum::RETURNED->value),
                        Forms\Components\Textarea::make('canceled_info')->label('سبب الإلغاء / الإعادة')
                    ])
                    ->action(function ($record, $data) {
                        DB::beginTransaction();
                        try {
                            $dataUpdate=['status' => $data['status'], 'canceled_info' => $data['canceled_info']];
                            if($data['status']==OrderStatusEnum::RETURNED->value){
                                $user=User::where([
                                    'level'=>LevelUserEnum::BRANCH->value,
                                    'branch_id' => $record->branch_source_id
                                ])->first()?->id;
                                $dataUpdate['given_id']=$user;
                                $dataUpdate['returned_id']=$record->pick_id;
                            }
                            $record->update($dataUpdate);
                            DB::commit();
                            Notification::make('success')->title('نجاح العملية')->body('تم تغيير حالة الطلب')->success()->send();
                        } catch (\Exception | Error $e) {
                            DB::rollBack();
                            Notification::make('error')->title('فشل العملية')->body($e->getMessage())->danger()->send();
                        }
                    })->label('الإلغاء / الإعادة')->button()->color('danger')
                    ->visible(fn($record) => $record->status !== OrderStatusEnum::SUCCESS && $record->status !== OrderStatusEnum::CANCELED && $record->status !== OrderStatusEnum::RETURNED && $record->status !== OrderStatusEnum::CONFIRM_RETURNED),

                Tables\Actions\Action::make('confirm_returned')
                    ->form(function($record){
                        $list=[];
                        if ($record->far_sender == false) {
                            if ($record->far > 0) {
                                $list[]=Forms\Components\Placeholder::make('far_usd')->content('سيتم إضافة  '.$record->far .' USD  إلى صندوقك  أجور شحن')->label('تحذير');
                            }
                            if ($record->far_tr > 0) {
                                $list[]=Forms\Components\Placeholder::make('far_try')->content('سيتم إضافة   '.$record->far_tr .' TRY  إلى صندوقك  أجور شحن')->label('تحذير');
                            }
                        }
                        if ($record->price > 0) {
                            $list[]=Forms\Components\Placeholder::make('price_usd')->content('سيتم إضافة  '.$record->price .' USD  إلى صندوقك  قيمة تحصيل')->label('تحذير');

                        }
                        if ($record->price_tr > 0) {
                            $list[]=Forms\Components\Placeholder::make('price_try')->content('سيتم إضافة  '.$record->price_tr .' TRY  إلى صندوقك  قيمة تحصيل')->label('تحذير');

                        }
                        return $list;
                    })
                    ->action(function ($record) {
                        DB::beginTransaction();
                        try {
                            $record->update(['status' => OrderStatusEnum::CONFIRM_RETURNED->value]);
                            HelperBalance::confirmReturn($record);
                            DB::commit();
                            Notification::make('success')->title('نجاح العملية')->body('تم تغيير حالة الطلب')->success()->send();
                        } catch (\Exception | Error $e) {
                            DB::rollBack();
                            Notification::make('error')->title('فشل العملية')->body($e->getMessage())->danger()->send();
                        }
                    })->label('تأكيد تسليم المرتجع')->color('danger')

                    ->visible(fn($record) => $record->status == OrderStatusEnum::RETURNED && $record->returned_id == auth()->id())

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            RelationManagers\AgenciesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
