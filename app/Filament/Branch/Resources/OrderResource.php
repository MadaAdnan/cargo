<?php

namespace App\Filament\Branch\Resources;

use App\Enums\ActivateStatusEnum;
use App\Enums\BayTypeEnum;
use App\Enums\FarType;
use App\Enums\LevelUserEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\TaskAgencyEnum;
use App\Filament\Branch\Resources\OrderResource\Pages;
use App\Filament\Branch\Resources\OrderResource\RelationManagers;
use App\Helper\HelperBalance;
use App\Models\Balance;
use App\Models\Branch;
use App\Models\City;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Error;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Notifications\Livewire\Notifications;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use LaraZeus\Popover\Tables\PopoverColumn;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $pluralModelLabel = 'الطلبات';

    protected static ?string $label = 'طلب';
    protected static ?string $navigationLabel = 'الطلبات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('الطلب')->schema([
//                    SpatieMediaLibraryFileUpload::make('images')->collection('images')->label('أرفق صور')->imageEditor(),
                    Forms\Components\Fieldset::make('معلومات المرسل')
                        ->schema([
                            Forms\Components\Grid::make()->schema([
                                Forms\Components\Select::make('type')->options([
                                    OrderTypeEnum::BRANCH->value => OrderTypeEnum::BRANCH->getLabel(),
                                    OrderTypeEnum::HOME->value => OrderTypeEnum::HOME->getLabel(),

                                ])->label('نوع الطلب')->default(OrderTypeEnum::HOME->value)
                                    ->required(),
                                Forms\Components\Select::make('sender_id')->relationship('sender', 'name')->label('معرف المرسل')->required()
                                    ->afterStateUpdated(function ($state, $set) {
                                        $user = User::with('city')->find($state);
                                        $branch = User::where(['level' => LevelUserEnum::BRANCH->value, 'branch_id' => $user->branch_id])->first()?->id;

                                        if ($user) {
                                            $set('sender_phone', $user?->phone);
                                            $set('sender_address', $user?->address);
                                            $set('city_source_id', $user?->city_id);
                                            $set('pick_id', $branch);

                                            /*

                                             $set('branch_source_id', $user?->branch_id);*/

                                        }
                                    })->live()->searchable()->suffixAction(Action::make('copyCostToPrice')->label('إضافة مستخدم جديد')
                                        ->icon('fas-user-plus')
                                        ->form(function () {
                                            $max = User::max('id') + 1;

                                            return [
                                                Forms\Components\Grid::make()->schema([
                                                    Forms\Components\TextInput::make('name')->label('الاسم')->required(),
                                                    Forms\Components\TextInput::make('email')->label('البريد الالكتروني')->email()->required()->unique(table: 'users', column: 'email')->default('user' . $max . '@gmail.com'),
                                                ]),
                                                Forms\Components\Grid::make()->schema([
                                                    Forms\Components\TextInput::make('username')->label('username')
                                                        ->unique(table: 'users', column: 'username')->required()->default('user' . $max),
                                                    Forms\Components\TextInput::make('password')->password()->dehydrateStateUsing(fn($state) => Hash::make($state))
                                                        ->label('كلمة المرور')->revealable()->required()->default(12345),

                                                ]),

                                                Forms\Components\Grid::make(2) // تقسيم الحقول إلى صفين
                                                ->schema([
                                                    Forms\Components\TextInput::make('phone_number')
                                                        ->label('رقم الهاتف')
                                                        ->placeholder('1234567890')
                                                        ->numeric() // التأكد أن الحقل يقبل الأرقام فقط
                                                        ->maxLength(15)
                                                        ->extraAttributes(['style' => 'text-align: left; direction: ltr;'])
                                                        ->tel()
                                                        ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),// تخصيص عرض حقل الرمز ومحاذاة النص لليسار
                                                        //H: Made phone number not required to complete account registration while creating an order
                                                        //->required(),

                                                    Forms\Components\TextInput::make('country_code')
                                                        ->label('رمز الدولة')
                                                        ->placeholder('963')
                                                        ->prefix('+')
                                                        ->maxLength(3)
                                                        ->numeric()
                                                        ->extraAttributes(['style' => 'text-align: left; direction: ltr; width: 100px;']), // تخصيص عرض حقل الرمز ومحاذاة النص لليسار
                                                        // تحديد الحد الأقصى للأرقام (بما في ذلك +)
                                                        //H: Made phone number not required to complete account registration while creating an order
                                                        //->required(),
                                                ]),
                                                Forms\Components\Grid::make()->schema([
                                                    Forms\Components\Textarea::make('address')->label('العنوان التفصيلي'),
                                                    Forms\Components\Select::make('city_id')->options(City::where('is_main', false)->pluck
                                                    ('name', 'id'))->required()
                                                        ->label('البلدة/البلدة')
                                                        ->searchable(),

                                                ]),

                                                Forms\Components\Grid::make()->schema([
                                                    Forms\Components\TextInput::make('full_name')->label('الاسم الكامل'),
                                                    Forms\Components\DatePicker::make('birth_date')->label('تاريخ الميلاد')
                                                        ->format('Y-m-d')->default(now()),

                                                ]),


                                            ];
                                        })
                                        ->action(function ($set, $data) {
                                            try {
                                                $data['level'] = LevelUserEnum::USER->value;
                                                $data['branch_id'] = City::find($data['city_id'])?->branch_id;
                                                $data['status'] = ActivateStatusEnum::ACTIVE->value;
                                                $data['phone'] = '+' . $data['country_code'] . $data['phone_number'];
                                                unset($data['country_code'], $data['phone_number']);
                                                while (true) {
                                                    $code = \Str::random(8);

                                                    $user = User::where('num_id', $code)->first();
                                                    if (!$user) {
                                                        $data['num_id'] = $code;
                                                        break;
                                                    }
                                                }

                                                $userNew = User::create($data);
                                                $set('sender_id', $userNew->id);
                                                $set('sender_name', $userNew->name);
                                                $set('sender_address', $userNew->address);
                                                $set('sender_phone', $userNew->phone);
                                                Notification::make('success')->title('نجاح العملية')->body("تم إضافة المستخدم بنجاح IBAN:{$userNew->iban}")->success()->send();
                                            } catch (\Exception | \Error $e) {
                                                Notification::make('error')->title('فشل العملية')->body($e->getMessage())->danger()->send();
                                            }

                                        })
                                    //
                                    ),
                            ]),
                            Forms\Components\Grid::make()->schema([
                                Forms\Components\TextInput::make('sender_phone')->label('رقم هاتف المرسل')->required(),

                                Forms\Components\TextInput::make('general_sender_name')->label('اسم المرسل'),

                            ]),
                            Forms\Components\Grid::make()->schema([
                                Forms\Components\Select::make('city_source_id')
                                    ->relationship('citySource', 'name')
                                    ->label('من بلدة')->reactive()->required()->searchable(),
                                //H: disabled required for sender address in order creation panel
                                Forms\Components\TextInput::make('sender_address')->label('عنوان المرسل'),
                                //->required(),
                            ]),

                        ]),
                    Forms\Components\Fieldset::make('معلومات المستلم')
                        ->schema([

                            Forms\Components\Grid::make()->schema([

                                Forms\Components\Select::make('receive_id')->label('معرف المستلم')
                                    ->options(User::all()->pluck('name', 'id')->toArray())->searchable()->default(fn() => User::where('email', 'zab@gmail.com')->first()?->id)
                                    ->afterStateUpdated(function ($state, $set) {
                                        $user = User::with('city')->find($state);
                                        if ($user) {

                                            $set('receive_phone', $user?->phone);
                                            $set('receive_address', $user?->address);

                                            $set('sender_name', $user?->name);
                                            $set('city_target_id', $user?->city_id);

                                            $set('receive_id', $user?->id);

                                        } else {
                                            $set('receive_phone', null);
                                            $set('receive_address', null);

                                            $set('sender_name', null);
                                            $set('city_target_id', null);
                                            $set('receive_id', null);


                                        }

                                    })->live(),
                                Forms\Components\Select::make('city_target_id')
                                    ->relationship('cityTarget', 'name')
                                    ->label('الى بلدة')->required()->searchable(),
                            ]),

                            Forms\Components\Grid::make()->schema([
                                Forms\Components\TextInput::make('receive_address')->label('عنوان المستلم'),
                                Forms\Components\TextInput::make('global_name')->label('اسم المستلم'),
                            ]),

                            Forms\Components\Grid::make()->schema([
                                Forms\Components\TextInput::make('receive_phone')->label('هاتف المستلم'),

                            ]),


                        ]),
                    Forms\Components\Fieldset::make('معلومات الطلب')
                        ->schema([
                            Forms\Components\Grid::make()->schema([
                                Forms\Components\Select::make('weight_id')
                                    ->relationship('weight', 'name')
                                    ->label
                                    ('الوزن')->searchable()->preload(),

                                Forms\Components\Select::make('size_id')
                                    ->relationship('size', 'name')
                                    ->label
                                    ('الحجم')->searchable()->preload(),
                            ]),
                            Forms\Components\Grid::make()->schema([
                                Forms\Components\Select::make('unit_id')
                                    ->relationship('unit', 'name')->label('الوحدة')->required(),
                            ]),

                        ]),
                    Forms\Components\Fieldset::make('الأجور')->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('price')->numeric()->label('التحصيل دولار')->default(0)->columnSpan(3)->visible(fn($context)=>$context=='create'),
                            Forms\Components\TextInput::make('far')->numeric()->label('أجور الشحن دولار')->default(0)->columnSpan(3)->visible(fn($context)=>$context=='create'),

                        ])->columnSpan(2),
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('price_tr')->numeric()->label('التحصيل تركي')->default(0)->columnSpan(3)->visible(fn($context)=>$context=='create'),
                            Forms\Components\TextInput::make('far_tr')->numeric()->label('أجور الشحن تركي')->default(0)->columnSpan(3)->visible(fn($context)=>$context=='create'),

                        ])->columnSpan(2),
                        Forms\Components\Grid::make()->schema([

                            Forms\Components\Select::make('pick_id')->label('الموظف الملتقط')->options(User::where('level',LevelUserEnum::BRANCH->value)->orWhere('level',LevelUserEnum::STAFF->value)->pluck('name','id'))->searchable()->required()->visible(fn($context)=>$context==='create'),

                        ]),

                        Forms\Components\Grid::make()->schema([
                            Forms\Components\Radio::make('far_sender')
                                ->options([
                                    true => 'المرسل',
                                    false => 'المستلم'
                                ])->required()->default(false)->inline(false)
                                ->label('أجور الشحن على')->visible(fn($context)=>$context==='create'),

                            Forms\Components\TextInput::make('canceled_info')
                                ->hidden(fn(Forms\Get $get): bool => !$get('active'))->live()
                                ->label('سبب الارجاع في حال ارجاع الطلب')->visible(fn($context)=>$context==='create'),
                        ])->visible(fn($context)=>$context==='create'),
                    ])->columns(4),


                    // ttrrtt


                ]),

                Forms\Components\Section::make('محتويات الطلب')
                    ->schema([
                        Forms\Components\Repeater::make('packages')->relationship('packages')->schema([
                            SpatieMediaLibraryFileUpload::make('package')->label('صورة الشحنة')->collection('packages'),
                            Forms\Components\TextInput::make('info')->label('معلومات الشحنة'),
                            Forms\Components\TextInput::make('quantity')->numeric()->label('الكمية'),
                        ])
                            ->label('محتويات الطلب')
                            ->addable(false)
                            ->collapsible()
                            ->collapsed()
                            ->deletable(false)->columnSpan(2)

                    ])->collapsible()->collapsed(true),
                Forms\Components\Section::make('سلسلة التوكيل')
                    ->schema([
                        Forms\Components\Repeater::make('agencies')->relationship('agencies')
                            ->schema([

                                Forms\Components\Select::make('user_id')->options(User::where(fn($query) => $query->where('level', LevelUserEnum::STAFF->value)
                                )->pluck('name', 'id'))->label('الموظف')->searchable(),
                                Forms\Components\Radio::make('status')->options([
                                    TaskAgencyEnum::TASK->value => TaskAgencyEnum::TASK->getLabel(),
                                    TaskAgencyEnum::TRANSPORT->value => TaskAgencyEnum::TRANSPORT->getLabel(),

                                ])->label('المهمة'),
                                Forms\Components\TextInput::make('task')->label('المهمة المطلوب تنفيذها'),

                            ])->defaultItems(1)
                            ->grid(2)
                            ->deletable(true)
                            ->addActionLabel('إضافة مهمة')
                            ->label('المهام')
                            ->itemLabel(fn(array $state): ?string => $state['package_name'] ?? ' مهمة...')->columnSpan(2), //
                        // استخدام اسم الشحنة كتسمية


                    ])->collapsible()->collapsed(true)->visible(false),


            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll(10)
            ->columns([

                PopoverColumn::make('qr_url')
                    ->trigger('click')
                    ->placement('right')
                    ->content(fn($record) => \LaraZeus\Qr\Facades\Qr::render($record->code))
                    ->icon('heroicon-o-qr-code'),

                Tables\Columns\TextColumn::make('code')->description(fn($record) => $record->id,'above')->copyable()->searchable(),


                Tables\Columns\TextColumn::make('type')->label('نوع الطلب')
                    ->description(fn($record) => $record->status?->getLabel())
                    ->extraCellAttributes(function($record){
                        $list=[];
                        switch ($record->status){
                            case OrderStatusEnum::PICK:
                                $list=['style'=>'background-color:yellow'];
                                break;
                            case OrderStatusEnum::TRANSFER:
                                $list=['style'=>'background-color:orange'];
                                break;
                            case OrderStatusEnum::RETURNED:
                                $list=['style'=>'background-color:red'];
                                break;
                            case OrderStatusEnum::CANCELED:
                                $list=['style'=>'background-color:gray;color:black'];
                                break;
                            case OrderStatusEnum::SUCCESS:
                                $list=['style'=>'background-color:green;'];
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

                Tables\Columns\TextColumn::make('unit.name')->label('الوحدة'),

                Tables\Columns\TextColumn::make('price')->formatStateUsing(fn($state)=>$state .' $ ')->label('التحصيل USD')->description(fn($record) => 'اجور الشحن : ' . $record->far.' $ '),
                Tables\Columns\TextColumn::make('price_tr')->formatStateUsing(fn($state)=>$state .'TRY')->label('التحصيل TRY')->description(fn($record) => 'اجور الشحن : ' . $record->far_tr .'TRY'),

                Tables\Columns\TextColumn::make('currency.name')->label('العملة'),
                Tables\Columns\TextColumn::make('sender.name')->label('اسم المرسل')->description(fn($record) => $record->general_sender_name)->searchable(),

                Tables\Columns\TextColumn::make('citySource.name')->label('من بلدة')->description(fn($record) => "إلى {$record->cityTarget?->name}")->searchable(),
                Tables\Columns\TextColumn::make('receive.name')->label('معرف المستلم ')->description(fn($record) => $record->global_name)->searchable(),
                Tables\Columns\TextColumn::make('receive_address')
                    ->formatStateUsing(fn($record) => (string)$record->receive_address . ' - ' . (string)$record->receive_phone)->label('هاتف المستلم ')
                    ->url(function ($record) {
                        $far = $record->far_sender ? 'على المرسل' : 'على المستلم';
                        $message = "السلام عليكم ورحمة الله وبركاته
                        %0a
                         لكم طلب مرسل عبر شركة الفاتح للنقل الداخلي
                         %0a
                           من : {$record->sender?->full_name}
                           %0a
                            إلى : {$record->global_name}
                       %0a
                        قيمة الطلب : {$record->price}
                        %0a
                        أجور الطلب : {$record->far}
                        %0a
                        الأجور : {$far}
                        %0a
                        يرجى تأكيد حضوركم وإرسال عنوان دقيق ليتم تسليمكم الطلب فيه مع إرفاق رقم البناء والشقة وإرفاق موقع GPS لتسريع الوصول للعنوان
                          %0a
                         ملاحظة : سيتم التوزيع خلال أقرب فرصة ممكنة إن شاء الله ";
                        return url('https://wa.me/' . ltrim($record?->receive_phone, '+') . '?text=' . $message);
                    })->openUrlInNewTab()
                    ->searchable(),

                Tables\Columns\TextColumn::make('pick.name')->formatStateUsing(fn($record)=>'موظف الإلتقاط : '.$record->pick?->name)->description(fn($record)=>'موظف التسليم : '.$record->given?->name)->label('التوكيل')


            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\Select::make('branch_source_id')->relationship('branchSource', 'name')
                            ->label('اسم الفرع المرسل')->multiple(),
                        Forms\Components\Select::make('branch_target_id')->relationship('branchTarget', 'name')
                            ->label('اسم الفرع المستلم')->multiple(),

                        Forms\Components\Select::make('receive_id')->relationship('receive', 'name')->label('اسم المستلم')->multiple(),
                        Forms\Components\Select::make('sender_id')->relationship('sender', 'name')->label('اسم المرسل')->multiple(),
                        Forms\Components\Select::make('status')->options([
                            OrderStatusEnum::PENDING->value => OrderStatusEnum::PENDING->getLabel(),
                            OrderStatusEnum::AGREE->value => OrderStatusEnum::AGREE->getLabel(),
                            OrderStatusEnum::PICK->value => OrderStatusEnum::PICK->getLabel(),
                            OrderStatusEnum::TRANSFER->value => OrderStatusEnum::TRANSFER->getLabel(),
                            OrderStatusEnum::SUCCESS->value => OrderStatusEnum::SUCCESS->getLabel(),
                            OrderStatusEnum::RETURNED->value => OrderStatusEnum::RETURNED->getLabel(),
                            OrderStatusEnum::CANCELED->value => OrderStatusEnum::CANCELED->getLabel(),


                        ])->label('حالة الطلب')->multiple(),
                        Forms\Components\Select::make('city_source_id')->relationship('citySource', 'name')
                            ->label('من بلدة')->multiple(),
                        Forms\Components\Select::make('city_target_id')->relationship('cityTarget', 'name')
                            ->label('الى بلدة')->multiple(),

                        Forms\Components\DatePicker::make('created_from')->label('من تاريخ'),
                        Forms\Components\DatePicker::make('created_until')->label('الى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['branch_target_id'],
                                fn(Builder $query, $date): Builder => $query->where('branch_target_id', $date),
                            )
                            ->when(
                                $data['branch_source_id'],
                                fn(Builder $query, $date): Builder => $query->where('branch_source_id', $date),
                            )
                            ->when(
                                $data['receive_id'],
                                fn(Builder $query, $date): Builder => $query->where('receive_id', $date),
                            )
                            ->when(
                                $data['sender_id'],
                                fn(Builder $query, $date): Builder => $query->where('sender_id', $date),
                            )
                            ->when(
                                $data['status'],
                                fn(Builder $query, $date): Builder => $query->where('status', $date),
                            )
                            ->when(
                                $data['city_target_id'],
                                fn(Builder $query, $date): Builder => $query->where('city_target_id', $date),
                            )
                            ->when(
                                $data['city_source_id'],
                                fn(Builder $query, $date): Builder => $query->where('city_source_id', $date),
                            )
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('set_picker')->form([

                        Forms\Components\Select::make('pick_id')
                            ->options(User::selectRaw('id,name')->where('level', LevelUserEnum::STAFF->value)->orWhere('level', LevelUserEnum::BRANCH->value)->pluck('name','id'))
                            ->searchable()->label('موظف الإلتقاط'),
                    ])
                        ->action(function ($record, $data) {
                            DB::beginTransaction();
                            try {
                                if ($record->pick_id == null) {
                                    $record->update(['pick_id' => $data['pick_id'], 'status' => OrderStatusEnum::AGREE->value]);
                                    HelperBalance::setPickOrder($record);
                                    Notification::make('success')->title('نجاح العملية')->body("تم تحديد موظف الإلتقاط بنجاح ")->success()->send();
                                    DB::commit();
                                }
                            } catch (\Exception $e) {
                                DB::rollBack();
                                Notification::make('error')->title('فشل العملية')->body("{$e->getMessage()}")->danger()->send();
                            }

                        })
                        ->visible(fn($record) => $record->pick_id == null)
                        ->label('تحديد موظف الإلتقاط')->color('info'),

                    Tables\Actions\Action::make('select_given_id')->form([
                        Forms\Components\Select::make('given_id')
                            ->options(User::selectRaw('id,name')->where('level', LevelUserEnum::STAFF->value)->orWhere('level', LevelUserEnum::BRANCH->value)->pluck('name','id'))
                            ->searchable()->label('موظف التسليم')
                    ])
                        ->action(function ($record, $data) {

                                $record->update(['given_id' => $data['given_id'], 'status' => OrderStatusEnum::TRANSFER->value]);
                                Notification::make('success')->title('نجاح العملية')->body('تم تحديد موظف التسليم بنجاح')->success()->send();

                        })
                        ->visible(fn($record) =>  $record->pick_id != null && ($record->status === OrderStatusEnum::PICK || $record->status === OrderStatusEnum::TRANSFER))
                        ->label('تحديد موظف التسليم')->color('info'),

                    // تحديد موظف غعادة الطلب
                    Tables\Actions\Action::make('set_returned_id')->form([
                        Forms\Components\Select::make('staff_id')->searchable()    ->getSearchResultsUsing(fn (string $search): array => User::where('name', 'like', "%{$search}%")->limit(50)->pluck('name', 'id')->toArray())->label('حدد الموظف')->required(),

                    ])
                        ->action(function($record,$data){
                            $record->update(['returned_id'=>$data['staff_id']]);
                            Notification::make('success')->title('نجاح العملية')->body('تم تحديد موظف إعادة الطلب بنجاح')->success()->send();

                        })
                        ->label('تحديد موظف تسليم المرتجع')->visible(fn($record)=>$record->status==OrderStatusEnum::RETURNED && $record->branch_source_id ==auth()->user()->branch_id),

                    Tables\Actions\Action::make('cancel_order')
                        ->form([
                            Forms\Components\Radio::make('status')->options([
                                OrderStatusEnum::CANCELED->value => OrderStatusEnum::CANCELED->getLabel(),
                                OrderStatusEnum::RETURNED->value => OrderStatusEnum::RETURNED->getLabel(),
                            ])->label('الحالة')->required()->default(OrderStatusEnum::CANCELED->value),
                            Forms\Components\Textarea::make('msg_cancel')->label('سبب الإلغاء / الإعادة')
                        ])
                        ->action(function ($record, $data) {
                            DB::beginTransaction();
                            try {
                                $record->update(['status' => $data['status'], 'canceled_info' => $data['msg_cancel']]);
                                DB::commit();
                                Notification::make('success')->title('نجاح العملية')->body('تم تغيير حالة الطلب')->success()->send();
                            } catch (\Exception | Error $e) {
                                DB::rollBack();
                                Notification::make('error')->title('فشل العملية')->body($e->getLine())->danger()->send();
                            }
                        })->label('الإلغاء / الإعادة')->color('danger')
                        ->visible(fn($record) => $record->status !== OrderStatusEnum::SUCCESS && $record->status !== OrderStatusEnum::CANCELED),


                    Tables\Actions\Action::make('success_pick')
                        ->form(function ($record) {
                            $farMessage='انت على وشك تأكيد إستلام مبلغ : ';
                            if ($record->far_sender == true && ($record->far>0 || $record->far_tr>0)) {

                                if($record->far_tr>0){

                                    $farMessage.=$record->far_tr.' TRY ';
                                }
                                if($record->far >0){
                                    $farMessage.=' و'.$record->far.' USD ';
                                }
                                $farMessage.='أجور شحن';
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
                        ->label('تأكيد إلتقاط الشحنة')->color('info')
                        ->visible(fn($record) => $record->pick_id == auth()->id() && ($record->status == OrderStatusEnum::AGREE ) ),

                    Tables\Actions\Action::make('success_given')
                        ->form(function ($record) {

                            $totalPrice=(double)$record->price+(double)$record->far;
                            if($totalPrice==0){
                                $totalPrice=(double) $record->price_tr+ (double) $record->far_tr;
                            }
                            $priceMessage='انت تأكد إستلامك مبلغ : ';


                            if($record->price_tr>0){
                                $priceMessage.=$record->price_tr .' TRY ';
                            }
                            if($record->price>0){
                                $priceMessage.=' و '.$record->price .' USD ';
                            }
                            $priceMessage.='قيمة تحصيل الطلب';



                            $farMessage='';

                            if($record->far_sender ==false){
                                $farMessage='انت تأكد إستلامك مبلغ : ';
                                if($record->far_tr>0){
                                    $farMessage.=$record->far_tr .' TRY ';
                                }
                                if($record->far>0){
                                    $farMessage.=' و '.$record->far .' USD ';
                                }
                                $farMessage.='أجور شحن الطلب';

                            }

                            if ($totalPrice > 0) {
                                $form= [
                                    Forms\Components\Placeholder::make('msg')->content($priceMessage)->extraAttributes(['style' => 'color:red;font-weight:900;font-size:1rem;'])->label('تنبيه'),
                                    Forms\Components\Placeholder::make('msg_2')->content($farMessage)->extraAttributes(['style' => 'color:red;font-weight:900;font-size:1rem;'])->label('تنبيه')
                                ];
                            }else{
                                $form= [
                                    Forms\Components\Placeholder::make('msg')->content("أنت على وشك تأكيد تسليم الطلب ")->extraAttributes(['style' => 'color:red;font-weight:900;font-size:1rem;'])->label('تنبيه')
                                ];
                            }
                            return $form;

                        })
                        ->action(function ($record) {
                            DB::beginTransaction();
                            try {
                                HelperBalance::completeOrder($record);
                                $record->update(['status' => OrderStatusEnum::SUCCESS->value]);
                                DB::commit();
                                Notification::make('success')->title('نجاح العملية')->body('تم تأكيد تسليم الطلب')->success()->send();
                            } catch (\Exception | Error $e) {
                                Notification::make('error')->title('فشل العملية')->body($e->getLine())->danger()->send();
                            }
                        })->label('تأكيد تسليم الشحنة')->color('info')
                        ->visible(fn($record) => $record->given_id == auth()->id() && ($record->status == OrderStatusEnum::TRANSFER || $record->status == OrderStatusEnum::PICK)),


                    Tables\Actions\Action::make('confirm_returned')
                        ->action(function ($record) {
                            DB::beginTransaction();
                            try {
                                $record->update(['status' =>OrderStatusEnum::CONFIRM_RETURNED->value]);
                                HelperBalance::confirmReturn($record);
                                DB::commit();
                                Notification::make('success')->title('نجاح العملية')->body('تم تغيير حالة الطلب')->success()->send();
                            } catch (\Exception | Error $e) {
                                DB::rollBack();
                                Notification::make('error')->title('فشل العملية')->body($e->getLine())->danger()->send();
                            }
                        })->label('تأكيد تسليم المرتجع')->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn($record) => $record->status == OrderStatusEnum::RETURNED && $record->returned_id==auth()->id())
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),


                    Tables\Actions\BulkAction::make('given_id_check')->form([
                        Forms\Components\Select::make('given_id')->options(User::where(fn($query) => $query->where('level', LevelUserEnum::STAFF->value)->orWhere('level', LevelUserEnum::BRANCH->value))->selectRaw('id,name,iban')->pluck('name', 'id'))->searchable()->label('موظف الإلتقاط')
                    ])
                        ->action(function ($records, $data) {
                            Order::whereNull('given_id')->whereIn('id', $records->pluck('id')->toArray())->update(['given_id' => $data['given_id'], 'status' => OrderStatusEnum::TRANSFER->value]);
                            Notification::make('success')->title('نجاح العملية')->body('تم تحديد موظف التسليم بنجاح')->success()->send();
                        })
                        ->label('تحديد موظف التسليم')->color('info')
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
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
