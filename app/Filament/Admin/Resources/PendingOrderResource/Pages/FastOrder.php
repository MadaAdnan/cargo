<?php

namespace App\Filament\Admin\Resources\PendingOrderResource\Pages;

use App\Enums\LevelUserEnum;
use App\Models\City;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use App\Enums\ActivateStatusEnum;
use App\Enums\TaskAgencyEnum;
use App\Models\Order;
use Carbon\Carbon;
use Error;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use App\Enums\OrderTypeEnum;
use App\Filament\Admin\Resources\PendingOrderResource;

class FastOrder extends CreatePendingOrder
{
    protected static string $resource = PendingOrderResource::class;
    protected static ?string $title = 'شحنة سريعة';

    public function form(Form $form): Form
    {
        $shipping = Order::latest()->first()?->shipping_date;
        $date = now()->format('Y-m-d');
        if ($shipping != null) {
            try {
                $date = Carbon::parse($shipping)->format('Y-m-d');
            } catch (\Exception | Error $e) {
            }
        }
        return $form
            ->schema([


                Forms\Components\Section::make('معلومات الطلب')->schema([
                    //                    SpatieMediaLibraryFileUpload::make('images')->collection('images')->label('أرفق صور')->imageEditor(),
                    Forms\Components\Fieldset::make('المرسل')->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([

                                // Forms\Components\Select::make('type')->options([
                                //     OrderTypeEnum::HOME->value => OrderTypeEnum::HOME->getLabel(),
                                //     OrderTypeEnum::BRANCH->value => OrderTypeEnum::BRANCH->getLabel(),
                                // ])->label('نوع الطلب')
                                //     ->required()
                                //     ->default(OrderTypeEnum::HOME->value)
                                //     ->reactive(),

                                Forms\Components\Hidden::make('type')
                                    ->default(OrderTypeEnum::HOME->value),

                                Forms\Components\Select::make('sender_id')
                                    // ->relationship('sender', 'name', fn($query) => $query->active())
                                    ->options(function () {
                                        $users = User::where('level', LevelUserEnum::USER->value)->get();
                                        foreach ($users as $user) {
                                            $options[$user->id] = $user->name;
                                        }
                                        return $options;
                                    })
                                    ->label('معرف المرسل')->required()
                                    ->afterStateUpdated(function ($state, $set) {
                                        $user = User::active()->with('city')->find($state);
                                        $branch = User::active()->where(['level' => LevelUserEnum::BRANCH->value, 'branch_id' => $user?->branch_id])->first()?->id;
                                        if ($user) {
                                            $set('sender_phone', $user?->phone);
                                            $set('sender_address', $user?->address);
                                            $set('city_source_id', $user?->city_id);
                                            $set('pick_id', $branch);
                                        }
                                    })->live()->visible(fn($context) => $context === 'create')
                                    ->searchable()
                                    ->noSearchResultsMessage('الاسم غير موجود')
                                    ->suffixAction(
                                        Action::make('copyCostToPrice')->label('إضافة مستخدم جديد')
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
                                                                ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'), // تخصيص عرض حقل الرمز ومحاذاة النص لليسار
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
                                                        Forms\Components\Select::make('city_id')->options(City::where('is_main', false)->pluck('name', 'id'))->required()
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
                                Forms\Components\Select::make('city_source_id')
                                    ->relationship('citySource', 'name')
                                    ->label('من بلدة')->reactive()->required()->searchable(),
                            ]),
                    ]),

                    Forms\Components\Fieldset::make('المستلم')->schema([
                        Forms\Components\Grid::make()->schema([

                            // Forms\Components\Select::make('receive_id')->label('معرف المستلم')->default(fn() => User::active()->where('email', 'zab@gmail.com')->first()?->id)
                            //     ->options(User::active()->where('level', LevelUserEnum::USER->value)->pluck('name', 'id')->toArray())->searchable()
                            //     ->afterStateUpdated(function ($state, $set) {
                            //         $user = User::with('city')->find($state);
                            //         if ($user) {
                            //             $set('receive_phone', $user?->phone);
                            //             $set('receive_address', $user?->address);

                            //             $set('sender_name', $user?->name);
                            //             $set('city_target_id', $user?->city_id);
                            //         }
                            //     })->live()->visible(fn($context) => $context === 'create'),

                            Forms\Components\Hidden::make('receive_id')
                                ->default(fn() => User::active()->where('email', 'zab@gmail.com')->first()?->id),



                            // Forms\Components\TextInput::make('receive_address')->label('عنوان المستلم'),

                        ]),
                        Forms\Components\Grid::make()->schema([
                            Forms\Components\Select::make('city_target_id')
                                ->relationship('cityTarget', 'name')
                                ->label('الى بلدة')->required()->searchable()->preload(),

                            Forms\Components\TextInput::make('receive_phone')->label('هاتف المستلم'),
                        ]),
                        Forms\Components\Grid::make()->schema([
                            Forms\Components\TextInput::make('global_name')->label('اسم المستلم'),

                        ]),
                    ]),

                    Forms\Components\Fieldset::make('معلومات الشحنة')->schema([

                        Forms\Components\Grid::make()->schema([
                            Forms\Components\Select::make('unit_id')
                                ->relationship('unit', 'name')->label('الوحدة')->required(),
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

                    Forms\Components\Fieldset::make('كود الشحنة')
                        ->schema([
                            Forms\Components\Toggle::make('allow_duplicates')
                                ->label('الشحنة مكودة')
                                ->default(true)
                                ->reactive()
                                ->dehydrated(false),
                            Forms\Components\TextInput::make('qr_code')
                                ->label('الكود')
                                ->rule(
                                    fn(callable $get) => $get('allow_duplicates')
                                        ? ['required', 'string', 'max:255', 'unique:orders,qr_code']
                                        : ['nullable', 'string', 'max:255']
                                )
                                ->columnSpan(1),
                        ])
                        ->columns(1)

                ])->collapsible(true)->collapsed(false),


                //
            ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
