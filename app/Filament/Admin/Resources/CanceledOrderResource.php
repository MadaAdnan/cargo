<?php

namespace App\Filament\Admin\Resources;

use App\Enums\ActivateStatusEnum;
use App\Enums\FarType;
use App\Enums\LevelUserEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\TaskAgencyEnum;
use App\Filament\Admin\Resources\CanceledOrderResource\Pages;
use App\Filament\Admin\Resources\CanceledOrderResource\RelationManagers;
use App\Helper\HelperBalance;
use App\Models\CanceledOrder;
use App\Models\City;
use App\Models\Order;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class CanceledOrderResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $pluralModelLabel = 'الطلبات';

    protected static ?string $label = 'شحنة';
    protected static ?string $navigationLabel = 'شحنات منتهية';
    protected static ?string $navigationGroup='الشحنات';

    protected static ?string $slug='canceled-orders';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'publish'
        ];
    }
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canForceDelete(Model $record): bool
    {
        return false;
    }

    public static function canForceDeleteAny(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        $shipping = Order::latest()->first()?->shipping_date;
        $date = now()->format('Y-m-d');
        if ($shipping != null) {
            try {
                $date = Carbon::parse($shipping)->format('Y-m-d');
            } catch (\Exception | \Error $e) {
            }
        }
        return $form
            ->schema([


                Forms\Components\Section::make('معلومات الطلب')->schema([
//                    SpatieMediaLibraryFileUpload::make('images')->collection('images')->label('أرفق صور')->imageEditor(),
                    Forms\Components\Fieldset::make('المرسل')->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([

                                Forms\Components\Select::make('type')->options([
                                    OrderTypeEnum::HOME->value => OrderTypeEnum::HOME->getLabel(),
                                    OrderTypeEnum::BRANCH->value => OrderTypeEnum::BRANCH->getLabel(),

                                ])->label('نوع الطلب')
                                    ->required()
                                    ->default(OrderTypeEnum::HOME->value)
                                    ->reactive(),

                                Forms\Components\Select::make('sender_id')
                                    ->relationship('sender', 'name', fn($query) => $query->active())
                                    ->label('معرف المرسل')->required()
                                    ->afterStateUpdated(function ($state, $set) {
                                        $user = User::active()->with('city')->find($state);
                                        $branch = User::active()->where(['level' => LevelUserEnum::BRANCH->value, 'branch_id' => $user->branch_id])->first()?->id;
                                        if ($user) {
                                            $set('sender_phone', $user?->phone);
                                            $set('sender_address', $user?->address);
                                            $set('city_source_id', $user?->city_id);
                                            $set('pick_id', $branch);


                                        }
                                    })->live()->visible(fn($context) => $context === 'create')
                                    ->searchable()
                                    ->noSearchResultsMessage('الاسم غير موجود')
                                    ->suffixAction(Action::make('copyCostToPrice')->label('إضافة مستخدم جديد')
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
                            Forms\Components\Select::make('city_source_id')
                                ->relationship('citySource', 'name')
                                ->label('من بلدة')->reactive()->required()->searchable(),
                            Forms\Components\TextInput::make('general_sender_name')->label('اسم المرسل'),


                        ]),
                        Forms\Components\Grid::make()->schema([


                            Forms\Components\TextInput::make('sender_phone')->label('رقم هاتف المرسل')->required(),
                            //H: disabled required for sender address in order creation panel
                            Forms\Components\TextInput::make('sender_address')->label('عنوان المرسل'),
                            //->required(),
                        ]),


                    ]),

                    Forms\Components\Fieldset::make('المستلم')->schema([
                        Forms\Components\Grid::make()->schema([
                            Forms\Components\Select::make('receive_id')->label('معرف المستلم')->default(fn() => User::active()->where('email', 'zab@gmail.com')->first()?->id)
                                ->options(User::active()->where('level', LevelUserEnum::USER->value)->pluck('name', 'id')->toArray())->searchable()
                                ->afterStateUpdated(function ($state, $set) {
                                    $user = User::with('city')->find($state);
                                    if ($user) {
                                        $set('receive_phone', $user?->phone);
                                        $set('receive_address', $user?->address);

                                        $set('sender_name', $user?->name);
                                        $set('city_target_id', $user?->city_id);
                                    }
                                })->live()->visible(fn($context) => $context === 'create'),
                            Forms\Components\Select::make('city_target_id')
                                ->relationship('cityTarget', 'name')
                                ->label('الى بلدة')->required()->searchable(),

                        ]),
                        Forms\Components\Grid::make()->schema([
                            Forms\Components\TextInput::make('receive_address')->label('عنوان المستلم'),
                            Forms\Components\TextInput::make('receive_phone')->label('هاتف المستلم'),

                        ]),
                        Forms\Components\Grid::make()->schema([
                            Forms\Components\TextInput::make('global_name')->label('اسم المستلم'),

                        ]),

                    ]),

                    Forms\Components\Fieldset::make('معلومات الشحنة')->schema([
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
                            Forms\Components\TextInput::make('note')->label('ملاحظات')
                        ]),
                        Forms\Components\Grid::make(1)->schema([
                            Forms\Components\DatePicker::make('shipping_date')->required()->label('تاريخ الشحنة')->default($date),
                        ]),
                    ]),
                    Forms\Components\Fieldset::make('الأجور')->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('price')->numeric()->label('التحصيل دولار')->default(0)->columnSpan(3)->visible(fn($context) => $context == 'create'),
                            Forms\Components\TextInput::make('far')->numeric()->label('أجور الشحن دولار')->default(0)->columnSpan(3)->visible(fn($context) => $context == 'create'),

                        ])->columnSpan(2),
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('price_tr')->numeric()->label('التحصيل تركي')->default(0)->columnSpan(3)->visible(fn($context) => $context == 'create'),
                            Forms\Components\TextInput::make('far_tr')->numeric()->label('أجور الشحن تركي')->default(0)->columnSpan(3)->visible(fn($context) => $context == 'create'),

                        ])->columnSpan(2),
                        Forms\Components\Grid::make()->schema([

                            Forms\Components\Select::make('pick_id')->label('الموظف الملتقط')->options(User::where('level', LevelUserEnum::BRANCH->value)->orWhere('level', LevelUserEnum::STAFF->value)->orWhere('level', LevelUserEnum::ADMIN->value)->pluck('name', 'id'))->searchable()->required()->visible(fn($context) => $context === 'create'),

                        ]),

                        Forms\Components\Grid::make()->schema([
                            Forms\Components\Radio::make('far_sender')
                                ->options([
                                    true => 'المرسل',
                                    false => 'المستلم'
                                ])->required()->default(false)->inline(false)
                                ->label('أجور الشحن على')->visible(fn($context) => $context === 'create'),

                            Forms\Components\TextInput::make('canceled_info')
                                ->hidden(fn(Forms\Get $get): bool => !$get('active'))->live()
                                ->label('سبب الارجاع في حال ارجاع الطلب')->visible(fn($context) => $context === 'create'),
                        ])->visible(fn($context) => $context === 'create'),
                    ])->columns(4),


                ])->collapsible(true)->collapsed(false),


                //
                Forms\Components\Section::make('محتويات الطلب')
                    ->schema([

                        Forms\Components\Grid::make()->schema([
                            Forms\Components\Repeater::make('packages')->relationship('packages')->schema([
                                SpatieMediaLibraryFileUpload::make('package')->label('صورة الشحنة')->collection('packages'),
                                Forms\Components\TextInput::make('info')->label('معلومات الشحنة'),
                                Forms\Components\TextInput::make('quantity')->numeric()->label('الكمية'),
                            ])
                                ->label('محتويات الطلب')
                                ->addable(false)
                                ->deletable(false)->columnSpan(2)
                                ->collapsible()
                                ->collapsed(),
                        ]),


                    ])->collapsible(true)->collapsed(true),
                Forms\Components\Section::make('سلسلة التوكيل')
                    ->schema([
                        Forms\Components\Repeater::make('agencies')->relationship('agencies')
                            ->schema([

                                Forms\Components\Select::make('user_id')->options(User::active()->where(fn($query) => $query->where('level', LevelUserEnum::STAFF->value)
                                )->pluck('name', 'id'))->label('الموظف')->searchable(),
                                Forms\Components\Radio::make('status')->options([
                                    TaskAgencyEnum::TASK->value => TaskAgencyEnum::TASK->getLabel(),
                                    TaskAgencyEnum::TRANSPORT->value => TaskAgencyEnum::TRANSPORT->getLabel(),

                                ])->label('المهمة'),
                                Forms\Components\TextInput::make('task')->label('المهمة المطلوب تنفيذها'),

                            ])->defaultItems(2)
                            ->deletable(true)
                            ->addActionLabel('إضافة مهمة')
                            ->label('المهام')
                            ->itemLabel(fn(array $state): ?string => $state['package_name'] ?? ' مهمة...')->columnSpan(2), //
                        // استخدام اسم الشحنة كتسمية


                    ])->collapsible(true)->collapsed(true)->visible(false),


            ]);

    }

    public static function table(Table $table): Table
    {
        $users = User::active()->selectRaw('id,name')->get();
        $cities = City::selectRaw('id,name,city_id')->get();

        return $table
//            ->poll(10)
            ->columns([
                //  Tables\Columns\SpatieMediaLibraryImageColumn::make('images')->collection('images')->circular()->openUrlInNewTab(),
                /*   PopoverColumn::make('qr_url')
                       ->trigger('click')
                       ->placement('right')
                       ->content(fn($record) => \LaraZeus\Qr\Facades\Qr::render($record->code))
                       ->icon('heroicon-o-qr-code'),*/

                Tables\Columns\TextColumn::make('id')->description(fn($record) => $record->code, 'above')->copyable()->searchable()->extraCellAttributes(fn(Model $record) => match ($record->color) {
                    'green' => ['style' => 'background-color:#55FF88;'],

                    default => ['style' => ''],
                }),
                Tables\Columns\TextColumn::make('createdBy.name')->label('أنشئ بواسطة'),


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
                                $list = ['style' => 'background-color:green;color:black'];
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

                Tables\Columns\TextColumn::make('price')->formatStateUsing(fn($state) => $state . ' $ ')->label('التحصيل USD')/*->description(fn($record) => 'اجور الشحن : ' . $record->far . ' $ ')*/,
                Tables\Columns\TextColumn::make('far')->formatStateUsing(fn($state) => $state . ' $ ')->label('الأجور USD')->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('price_tr')->formatStateUsing(fn($state) => $state . 'TRY')->label('التحصيل TRY')/*->description(fn($record) => 'اجور الشحن : ' . $record->far_tr . 'TRY')*/,
                Tables\Columns\TextColumn::make('far_tr')->formatStateUsing(fn($state) => $state . 'TRY')->label('الأجور TRY')->toggleable(isToggledHiddenByDefault: false)/*->description(fn($record) => 'اجور الشحن : ' . $record->far_tr . 'TRY')*/,

                Tables\Columns\TextColumn::make('currency.name')->label('العملة'),

                Tables\Columns\TextColumn::make('sender.name')->label('اسم المرسل')->description(fn($record) => $record->general_sender_name)->searchable(),

                Tables\Columns\TextColumn::make('citySource.name')->label('من بلدة')->description(fn($record) => " {$record->citySource?->city?->name}")->searchable(),
                Tables\Columns\TextColumn::make('cityTarget.name')->label('إلى بلدة')->description(fn($record) => " {$record->cityTarget?->city?->name}")->searchable(),
                Tables\Columns\TextColumn::make('branchSource.name')->label('من فرع')->description(fn($record) => "إلى فرع  {$record->branchTarget?->name}")->searchable(),


                Tables\Columns\TextColumn::make('global_name')->label('معرف المستلم ')->description(fn($record) => $record->receive?->name)->searchable(),
                Tables\Columns\TextColumn::make('receive_phone')
                    ->formatStateUsing(fn($record) => (string)$record->receive_address . ' - ' . (string)$record->receive_phone)->label('هاتف المستلم ')
                    /*->description(fn($record) =>  ltrim($record?->receive_phone, '+'))*/
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
                    ->searchable()->color('danger'),
                Tables\Columns\TextColumn::make('pick.name')->formatStateUsing(fn($record) => 'موظف الإلتقاط : ' . $record->pick?->name)
                    ->description(fn($record) => 'موظف التسليم : ' . $record->given?->name)->label('التوكيل'),
                Tables\Columns\TextColumn::make('note')->label('ملاحظات')->color('primary'),
                Tables\Columns\TextColumn::make('shipping_date')->date('y-m-d')->label('تاريخ الشحنة'),
                Tables\Columns\TextColumn::make('created_at')->date('Y-m-d')->label('تاريخ إنشاء الشحنة')->extraCellAttributes(fn(Model $record) => match ($record->color) {
                    'green' => ['style' => 'background-color:#55FF88;'],

                    default => ['style' => ''],
                })


            ])->defaultSort('created_at', 'desc')
            ->filters([
//

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\Select::make('branch_source_id')->relationship('branchSource', 'name')
                            ->label('اسم الفرع المرسل')->multiple(),
                        //H: added delivery employee filter to table
                        Forms\Components\Select::make('pick_id')
                            ->label('اسم موظف الإلتقاط')
                            ->options($users->pluck('name', 'id'))
                            ->multiple(),
                        Forms\Components\Select::make('given_id')
                            ->label('اسم موظف التسليم')
                            ->options($users->pluck('name', 'id'))
                            ->multiple(),


                        Forms\Components\Select::make('branch_target_id')->relationship('branchTarget', 'name')
                            ->label('اسم الفرع المستلم')->multiple(),

                        Forms\Components\Select::make('receive_id')->options($users->pluck('name', 'id'))->label('اسم المستلم')->multiple(),
                        Forms\Components\Select::make('sender_id')->options($users->pluck('name', 'id'))->label('اسم المرسل')->multiple(),
                        Forms\Components\Select::make('status')->options([
                            OrderStatusEnum::PENDING->value => OrderStatusEnum::PENDING->getLabel(),
                            OrderStatusEnum::AGREE->value => OrderStatusEnum::AGREE->getLabel(),
                            OrderStatusEnum::PICK->value => OrderStatusEnum::PICK->getLabel(),
                            OrderStatusEnum::TRANSFER->value => OrderStatusEnum::TRANSFER->getLabel(),
                            OrderStatusEnum::SUCCESS->value => OrderStatusEnum::SUCCESS->getLabel(),
                            OrderStatusEnum::RETURNED->value => OrderStatusEnum::RETURNED->getLabel(),
                            OrderStatusEnum::CANCELED->value => OrderStatusEnum::CANCELED->getLabel(),


                        ])->label('حالة الطلب')->multiple(),
                        Forms\Components\Select::make('area_source')->options(City::where('is_main', '=', 1)->pluck('name', 'id'))
                            ->label('من منطقة')->live(),
                        Forms\Components\Select::make('area_target')->options(City::where('is_main', '=', 1)->pluck('name', 'id'))
                            ->label('إلى منطقة')->live(),
                        Forms\Components\Select::make('city_source_id')->options($cities->pluck('name', 'id'))
                            ->label('من بلدة')->multiple(),
                        Forms\Components\Select::make('city_target_id')->options($cities->pluck('name', 'id'))
                            ->label('الى بلدة')->multiple(),

                        Forms\Components\DatePicker::make('created_from')->label('من تاريخ'),
                        Forms\Components\DatePicker::make('created_until')->label('الى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['area_source'],
                                fn(Builder $query, $date): Builder => $query->whereHas('citySource', fn($query) => $query->where('cities.city_id', $date)),
                            )
                            ->when(
                                $data['area_target'],
                                fn(Builder $query, $date): Builder => $query->whereHas('cityTarget', fn($query) => $query->where('cities.city_id', $date)),
                            )
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
                            )
                            //H: added the logic to quEry
                            ->when(
                                $data['pick_id'],
                                fn(Builder $query, $value): Builder => $query->where('pick_id', $value),
                            )
                            ->when(
                                $data['given_id'],
                                fn(Builder $query, $value): Builder => $query->where('given_id', $value),
                            );
                    })

            ])
            //->filtersFormMaxHeight('300px')
            ->actions([


                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('set_picker')->form([
                        Forms\Components\Select::make('pick_id')
                            ->options(User::selectRaw('id,name')->whereIn('level', [
                                LevelUserEnum::STAFF->value,
                                LevelUserEnum::BRANCH->value,
                                LevelUserEnum::ADMIN->value,
                            ])->pluck('name', 'id'))
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

                    Tables\Actions\Action::make('set_given')->form([
                        Forms\Components\Select::make('given_id')
                            ->searchable()
                            ->getSearchResultsUsing(fn(string $search) => User::active()->selectRaw('id,name')->whereIn('level', [
                                LevelUserEnum::STAFF->value,
                                LevelUserEnum::BRANCH->value,
                                LevelUserEnum::ADMIN->value,
                            ])->where('name', 'like', "%$search%")->take(10)->pluck('name', 'id'))
                            ->label('موظف التسليم'),
                    ])
                        ->action(function ($record, $data) {

                            $record->update(['given_id' => $data['given_id'], 'status' => OrderStatusEnum::TRANSFER->value]);
                            Notification::make('success')->title('نجاح العملية')->body("تم تحديد موظف التسليم بنجاح ")->success()->send();


                        })
                        ->visible(fn($record) => $record->given_id == null && ($record->status === OrderStatusEnum::PICK || $record->status === OrderStatusEnum::TRANSFER))
                        ->label('تحديد موظف التسليم')->color('info'),

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
                        ->label('تأكيد إلتقاط الشحنة')->color('info')
                        ->visible(fn($record) => $record->pick_id != null && ($record->status == OrderStatusEnum::AGREE)),

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
                        })->label('تأكيد تسليم الشحنة')->color('info')
                        ->visible(fn($record) => $record->given_id != null && ($record->status == OrderStatusEnum::TRANSFER)),


                    Tables\Actions\Action::make('cancel_order')
                        ->form([
                            Forms\Components\Radio::make('status')->options(function(){
                                $list=[

                                    OrderStatusEnum::RETURNED->value => OrderStatusEnum::RETURNED->getLabel(),
                                ];
                                if(auth()->user()->hasRole('مدير عام')){
                                    $list[OrderStatusEnum::CANCELED->value] = OrderStatusEnum::CANCELED->getLabel();
                                }
                                return $list;
                            })->label('الحالة')->required()->default(!auth()->user()->hasRole('مدير عام')?OrderStatusEnum::RETURNED->value:OrderStatusEnum::CANCELED->value),
                            Forms\Components\Textarea::make('canceled_info')->label('سبب الإلغاء / الإعادة')
                        ])
                        ->action(function ($record, $data) {
                            DB::beginTransaction();
                            try {
                                $dataUpdate = ['status' => $data['status'], 'canceled_info' => $data['canceled_info']];
                                if ($data['status'] == OrderStatusEnum::RETURNED->value) {
                                    $user = User::where([
                                        'level' => LevelUserEnum::BRANCH->value,
                                        'branch_id' => $record->branch_source_id
                                    ])->first()?->id;
                                    $dataUpdate['given_id'] = $user;
                                    $dataUpdate['returned_id'] = $record->pick_id;
                                }
                                /**
                                 * @var $record Order
                                 */
                                $record->update($dataUpdate);
                                DB::commit();
                                Notification::make('success')->title('نجاح العملية')->body('تم تغيير حالة الطلب')->success()->send();
                            } catch (\Exception | Error $e) {
                                DB::rollBack();
                                Notification::make('error')->title('فشل العملية')->body($e->getLine())->danger()->send();
                            }
                        })->label('الإلغاء / الإعادة')->color('danger')
                        ->visible(fn($record) => $record->status !== OrderStatusEnum::SUCCESS && $record->status !== OrderStatusEnum::CANCELED && $record->status !== OrderStatusEnum::RETURNED && $record->status !== OrderStatusEnum::CONFIRM_RETURNED ),
                    // تحديد موظف غعادة الطلب
                    Tables\Actions\Action::make('set_returned_id')->form([
                        Forms\Components\Select::make('staff_id')->searchable()->getSearchResultsUsing(fn(string $search): array => User::where('name', 'like', "%{$search}%")->limit(50)->pluck('name', 'id')->toArray())->label('حدد الموظف')->required(),

                    ])
                        ->action(function ($record, $data) {
                            $record->update(['returned_id' => $data['staff_id']]);
                            Notification::make('success')->title('نجاح العملية')->body('تم تحديد موظف إعادة الطلب بنجاح')->success()->send();

                        })
                        ->label('تحديد موظف تسليم المرتجع')
                        ->visible(fn($record) => $record->status == OrderStatusEnum::RETURNED && $record->returned_id == null),

                    /* Tables\Actions\Action::make('confirm_returned')
                         ->form(function ($record) {
                             $list = [];
                             if ($record->far_sender == false) {
                                 if ($record->far > 0) {
                                     $list[] = Forms\Components\Placeholder::make('far_usd')->content('سيتم إضافة  ' . $record->far . ' USD  إلى صندوقك  أجور شحن')->label('تحذير');
                                 }
                                 if ($record->far_tr > 0) {
                                     $list[] = Forms\Components\Placeholder::make('far_try')->content('سيتم إضافة   ' . $record->far_tr . ' TRY  إلى صندوقك  أجور شحن')->label('تحذير');
                                 }
                             }
                             if ($record->price > 0) {
                                 $list[] = Forms\Components\Placeholder::make('price_usd')->content('سيتم إضافة  ' . $record->price . ' USD  إلى صندوقك  قيمة تحصيل')->label('تحذير');

                             }
                             if ($record->price_tr > 0) {
                                 $list[] = Forms\Components\Placeholder::make('price_try')->content('سيتم إضافة  ' . $record->price_tr . ' TRY  إلى صندوقك  قيمة تحصيل')->label('تحذير');

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
                                 Notification::make('error')->title('فشل العملية')->body($e->getLine())->danger()->send();
                             }
                         })->label('تأكيد تسليم المرتجع')->color('danger')
                         ->visible(fn($record) => $record->status == OrderStatusEnum::RETURNED && $record->returned_id != null),*/
                    // Tables\Actions\Action::make('check_green')->action(fn($record) => $record->update(['color' => 'green']))->label('تعيين باللون الاخضر')->visible(fn($record) => $record->color == null)

                ])

//                Tables\Actions\DeleteAction::make(),

            ])
            ->headerActions([
                ExportAction::make()->exports([
                    ExcelExport::make()->withChunkSize(100)->fromTable()
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('cancel_order')->action(function ($records) {
                        foreach ($records as $record) {
                            $record->update(['status' => OrderStatusEnum::CANCELED->value]);
                            Notification::make('success')->title('نجاح')->body('تم إلغاء الشحنات بنجاح')->success()->send();

                        }
                    })->label('إلغاء الشحنات')->visible(auth()->user()->hasRole('super_admin')),
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('given_id_check')->form([
                        Forms\Components\Select::make('given_id')
                            ->options(User::active()->where('users.level', LevelUserEnum::STAFF->value)->orWhere('users.level', LevelUserEnum::BRANCH->value)->orWhere('users.level', LevelUserEnum::ADMIN->value)->pluck('name', 'id'))
                            ->searchable()->label('موظف التسليم')
                    ])
                        ->action(function ($records, $data) {

                            DB::table('orders')->whereIn('id', $records->pluck('id')->toArray())->update(['given_id' => $data['given_id'], 'status' => OrderStatusEnum::TRANSFER->value]);
                            Notification::make('success')->title('نجاح العملية')->body('تم تحديد موظف التسليم بنجاح')->success()->send();
                        })
                        ->label('تحديد موظف التسليم')->color('info'),

                    Tables\Actions\BulkAction::make('returned_confirm_all')->action(function ($records) {
                        DB::beginTransaction();
                        try {
                            foreach ($records as $record) {
                                $record->update(['status' => OrderStatusEnum::CONFIRM_RETURNED->value]);
                                HelperBalance::confirmReturn($record);
                            }
                            DB::commit();
                            Notification::make('success')->title('نجاح العملية')->body('تم تغيير حالة الطلب')->success()->send();
                        } catch (\Exception | Error $e) {
                            DB::rollBack();
                            Notification::make('error')->title('فشل العملية')->body($e->getLine())->danger()->send();
                        }
                    })->label('تأكيد تسليم المرتجع')->requiresConfirmation() ,
//                    ExportBulkAction::make()

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
            'index' => Pages\ListCanceledOrders::route('/'),
            'create' => Pages\CreateCanceledOrder::route('/create'),
            'edit' => Pages\EditCanceledOrder::route('/{record}/edit'),
        ];
    }
}
