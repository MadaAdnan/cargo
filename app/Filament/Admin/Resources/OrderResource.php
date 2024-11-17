<?php

namespace App\Filament\Admin\Resources;

use App\Enums\ActivateStatusEnum;
use App\Enums\CategoryTypeEnum;
use App\Enums\FarType;
use App\Enums\JobUserEnum;
use App\Enums\LevelUserEnum;
use App\Enums\TaskAgencyEnum;
use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Filament\Admin\Resources\OrderResource\RelationManagers;
use App\Helper\HelperBalance;
use App\Models\Branch;
use App\Models\City;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Error;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use LaraZeus\Popover\Tables\PopoverColumn;
use App\Enums\OrderTypeEnum;
use App\Enums\OrderStatusEnum;
use Filament\Forms\Components\Tabs;
use App\Enums\BayTypeEnum;
use Filament\Infolists\Infolist;
use PhpOffice\PhpSpreadsheet\Calculation\LookupRef\Selection;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $pluralModelLabel = 'الطلبات';

    protected static ?string $label = 'شحنة';
    protected static ?string $navigationLabel = 'الشحنات';
    protected static ?string $navigationIcon = 'heroicon-o-truck';

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
                                    ->relationship('sender', 'name')
                                    ->label('معرف المرسل')->required()
                                    ->afterStateUpdated(function ($state, $set) {
                                        $user = User::with('city')->find($state);
                                        $branch=User::where(['level'=>LevelUserEnum::BRANCH->value,'branch_id' => $user->branch_id])->first()?->id;
                                        if ($user) {
                                            $set('sender_phone', $user?->phone);
                                            $set('sender_address', $user?->address);
                                            $set('city_source_id', $user?->city_id);
                                            $set('pick_id', $branch);


                                        }
                                    })->live()->visible(fn($context)=>$context==='create')
                                    ->searchable()
                                    ->noSearchResultsMessage('الاسم غير موجود')
                                    ->suffixAction(Action::make('copyCostToPrice')->label('إضافة مستخدم جديد')
                                        ->icon('fas-user-plus')
                                        ->form(function(){
                                            $max=User::max('id')+1;

                                            return [
                                                Forms\Components\Grid::make()->schema([
                                                    Forms\Components\TextInput::make('name')->label('الاسم')->required(),
                                                    Forms\Components\TextInput::make('email')->label('البريد الالكتروني')->email()->required()->unique(table: 'users', column: 'email')->default('user'. $max.'@gmail.com'),
                                                ]),
                                                Forms\Components\Grid::make()->schema([
                                                    Forms\Components\TextInput::make('username')->label('username')
                                                        ->unique(table: 'users', column: 'username')->required()->default('user'. $max),
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
                                                        ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')// تخصيص عرض حقل الرمز ومحاذاة النص لليسار
                                                        ->required(),

                                                    Forms\Components\TextInput::make('country_code')
                                                        ->label('رمز الدولة')
                                                        ->placeholder('963')
                                                        ->prefix('+')
                                                        ->maxLength(3)
                                                        ->numeric()
                                                        ->extraAttributes(['style' => 'text-align: left; direction: ltr; width: 100px;']) // تخصيص عرض حقل الرمز ومحاذاة النص لليسار
                                                        // تحديد الحد الأقصى للأرقام (بما في ذلك +)
                                                        ->required(),
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

                            Forms\Components\TextInput::make('sender_address')->label('عنوان المرسل')->required(),
                        ]),


                    ]),

                    Forms\Components\Fieldset::make('المستلم')->schema([
                        Forms\Components\Grid::make()->schema([
                            Forms\Components\Select::make('receive_id')->label('معرف المستلم')->default(fn() => User::where('email', 'zab@gmail.com')->first()?->id)
                                ->options(User::all()->pluck('name', 'id')->toArray())->searchable()
                                ->afterStateUpdated(function ($state, $set) {
                                    $user = User::with('city')->find($state);
                                    if ($user) {
                                        $set('receive_phone', $user?->phone);
                                        $set('receive_address', $user?->address);

                                        $set('sender_name', $user?->name);
                                        $set('city_target_id', $user?->city_id);
                                    }
                                })->live() ->visible(fn($context)=>$context==='create'),
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

                                Forms\Components\Select::make('user_id')->options(User::where(fn($query) => $query->where('level', LevelUserEnum::STAFF->value)
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
$users=User::selectRaw('id,name')->get();
$cities=City::selectRaw('id,name')->get();
        return $table
            ->poll(10)
            ->columns([
             //  Tables\Columns\SpatieMediaLibraryImageColumn::make('images')->collection('images')->circular()->openUrlInNewTab(),
                PopoverColumn::make('qr_url')
                    ->trigger('click')
                    ->placement('right')
                    ->content(fn($record) => \LaraZeus\Qr\Facades\Qr::render($record->code))
                    ->icon('heroicon-o-qr-code'),

                Tables\Columns\TextColumn::make('id')->description(fn($record) => $record->code,'above')->copyable()->searchable(),


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
                               $list=['style'=>'background-color:green;color:black'];
                               break;
                       }
                       return $list;
                   }),
                Tables\Columns\TextColumn::make('far_sender')->formatStateUsing(fn($state)=>FarType::tryFrom($state)?->getLabel())
                    ->color(fn($state)=>FarType::tryFrom($state)?->getColor())
                    ->icon(fn($state)=>FarType::tryFrom($state)?->getIcon())
                    ->label('حالة الدفع')
                    ->description(fn($record) => $record->created_at->diffForHumans())
                    ->searchable(),

                Tables\Columns\TextColumn::make('unit.name')->label('نوع الشحنة'),

                Tables\Columns\TextColumn::make('price')->formatStateUsing(fn($state)=>$state .' $ ')->label('التحصيل USD')->description(fn($record) => 'اجور الشحن : ' . $record->far.' $ '),
                Tables\Columns\TextColumn::make('price_tr')->formatStateUsing(fn($state)=>$state .'TRY')->label('التحصيل TRY')->description(fn($record) => 'اجور الشحن : ' . $record->far_tr .'TRY'),

                Tables\Columns\TextColumn::make('currency.name')->label('العملة'),

                Tables\Columns\TextColumn::make('sender.name')->label('اسم المرسل')->description(fn($record) => $record->general_sender_name)->searchable(),

                Tables\Columns\TextColumn::make('citySource.name')->label('من بلدة')->description(fn($record) => " {$record->citySource?->city?->name}")->searchable(),
                Tables\Columns\TextColumn::make('cityTarget.name')->label('إلى بلدة')->description(fn($record) => " {$record->cityTarget?->city?->name}")->searchable(),
                Tables\Columns\TextColumn::make('branchSource.name')->label('من فرع')->description(fn($record) => "إلى فرع  {$record->branchTarget?->name}")->searchable(),


                Tables\Columns\TextColumn::make('global_name')->label('معرف المستلم ')->description(fn($record) => $record->receive?->name)->searchable(),
                Tables\Columns\TextColumn::make('receive_phone')
                    ->formatStateUsing(fn($record)=>(string) $record->receive_address .' - '.(string) $record->receive_phone)->label('هاتف المستلم ')
                    /*->description(fn($record) =>  ltrim($record?->receive_phone, '+'))*/
                    ->url(function($record) {
                      $far=  $record->far_sender?'على المرسل':'على المستلم';

                        $message="السلام عليكم ورحمة الله وبركاته
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
                        return url('https://wa.me/' . ltrim($record?->receive_phone, '+').'?text='.$message);
                    })->openUrlInNewTab()
                    ->searchable()->color('danger'),
                Tables\Columns\TextColumn::make('pick.name')->formatStateUsing(fn($record)=>'موظف الإلتقاط : '.$record->pick?->name)->description(fn($record)=>'موظف التسليم : '.$record->given?->name)->label('التوكيل')


            ])->defaultSort('created_at', 'desc')
            ->filters([
//

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\Select::make('branch_source_id')->relationship('branchSource', 'name')
                            ->label('اسم الفرع المرسل')->multiple(),
                        Forms\Components\Select::make('branch_target_id')->relationship('branchTarget', 'name')
                            ->label('اسم الفرع المستلم')->multiple(),

                        Forms\Components\Select::make('receive_id')->options($users->pluck('name','id'))->label('اسم المستلم')->multiple(),
                        Forms\Components\Select::make('sender_id')->options($users->pluck('name','id'))->label('اسم المرسل')->multiple(),
                        Forms\Components\Select::make('status')->options([
                            OrderStatusEnum::PENDING->value => OrderStatusEnum::PENDING->getLabel(),
                            OrderStatusEnum::AGREE->value => OrderStatusEnum::AGREE->getLabel(),
                            OrderStatusEnum::PICK->value => OrderStatusEnum::PICK->getLabel(),
                            OrderStatusEnum::TRANSFER->value => OrderStatusEnum::TRANSFER->getLabel(),
                            OrderStatusEnum::SUCCESS->value => OrderStatusEnum::SUCCESS->getLabel(),
                            OrderStatusEnum::RETURNED->value => OrderStatusEnum::RETURNED->getLabel(),
                            OrderStatusEnum::CANCELED->value => OrderStatusEnum::CANCELED->getLabel(),


                        ])->label('حالة الطلب')->multiple(),
                        Forms\Components\Select::make('city_source_id')->options($cities->pluck('name','id'))
                            ->label('من بلدة')->multiple(),
                        Forms\Components\Select::make('city_target_id')->options($cities->pluck('name','id'))
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

            ])->filtersFormMaxHeight('300px')
            ->actions([


                Tables\Actions\ViewAction::make(),
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
                                if($record->pick_id==null){
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
                            ->options(User::selectRaw('id,name')->where('level', LevelUserEnum::STAFF->value)->orWhere('level', LevelUserEnum::BRANCH->value)->pluck('name','id'))
                            ->searchable()->label('موظف الإلتقاط'),
                    ])
                        ->action(function ($record, $data) {

                                $record->update(['given_id' => $data['given_id'],'status'=>OrderStatusEnum::TRANSFER->value]);
                                Notification::make('success')->title('نجاح العملية')->body("تم تحديد موظف التسليم بنجاح ")->success()->send();



                        })
                        ->visible(fn($record) =>  $record->pick_id == null && ($record->status === OrderStatusEnum::PICK || $record->status === OrderStatusEnum::TRANSFER))
                        ->label('تحديد موظف التسليم')->color('info'),

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
                        ->visible(fn($record) =>  $record->pick_id !=null&& ($record->status == OrderStatusEnum::AGREE) ),

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
                        ->visible(fn($record) => $record->given_id !=null && ($record->status == OrderStatusEnum::TRANSFER)),


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
                        ->visible(fn($record) => $record->status !== OrderStatusEnum::SUCCESS && $record->status !== OrderStatusEnum::CANCELED)

                ])

//                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('given_id_check')->form([
                        Forms\Components\Select::make('given_id')->options(User::where('users.level', LevelUserEnum::STAFF->value)->orWhere('users.level', LevelUserEnum::BRANCH->value)->selectRaw('id,name,iban')->get()->mapWithKeys(fn($user) => [$user->id => $user->iban_name]))->searchable()->label('موظف الإلتقاط')
                    ])
                        ->action(function ($records, $data) {
                            Order::whereNull('given_id')->whereIn('id', $records->pluck('id')->toArray())->update(['given_id' => $data['given_id'],'status'=>OrderStatusEnum::TRANSFER->value]);
                            Notification::make('success')->title('نجاح العملية')->body('تم تحديد موظف التسليم بنجاح')->success()->send();
                        })
                        ->label('تحديد موظف التسليم')->color('info')
//                    ExportBulkAction::make()

                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AgenciesRelationManager::class,
        ];
    }

    /*public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }*/

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
