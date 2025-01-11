<?php

/** @noinspection ALL */

/** @noinspection PhpUndefinedClassInspection */

namespace App\Filament\Admin\Resources;

use App\Enums\BalanceTypeEnum;
use App\Filament\Admin\Resources\UserResource\Pages;
use App\Filament\Admin\Resources\UserResource\RelationManagers;
use App\Enums\JobUserEnum;
use App\Enums\LevelUserEnum;
use App\Helper\HelperBalance;
use App\Models\Balance;
use App\Models\Branch;
use App\Enums\ActivateStatusEnum;
use App\Models\City;
use Dotswan\MapPicker\Fields\Map;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Tabs;

use PHPUnit\Exception;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;


class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $pluralModelLabel = 'المستخدمون';

    protected static ?string $label = 'مستخدم';
    protected static ?string $navigationLabel = 'المستخدمون';
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        $max = User::max('id') + 1;
        return $form
            ->schema([

                Forms\Components\Tabs::make('Tabs')->tabs(
                    [
                        Tabs\Tab::make('المعلومات الاساسية')
                            ->schema([
                                Forms\Components\CheckboxList::make('roles')
                                    ->relationship('roles', 'name', fn($query) => $query->when(!auth()->user()->hasRole('super_admin'), fn($query) => $query->where('name', '!=', 'super_admin')))->label('الصلاحيات'),
                                Forms\Components\TextInput::make('name')->label('الاسم')->required(),
                                Forms\Components\TextInput::make('email')->label('البريد الالكتروني')->email()->required()->unique(ignoreRecord: true)->default('user' . $max . '@gmail.com'),
                                Forms\Components\TextInput::make('username')->label('username')
                                    ->unique(ignoreRecord: true)->required()->default('user' . $max),
                                Forms\Components\TextInput::make('password')->password()->dehydrateStateUsing(fn($state) => Hash::make($state))
                                    ->dehydrated(fn($state) => filled($state))->label('كلمة المرور')->revealable()->default('12345'),


                                //                                Forms\Components\TextInput::make('phone')->label('الهاتف')->tel()->required(),
                                Forms\Components\Grid::make(2) // تقسيم الحقول إلى صفين
                                ->schema([

                                    Forms\Components\TextInput::make('phone_number')
                                        ->label('رقم الهاتف')
                                        ->placeholder('1234567890')
                                        ->numeric() // التأكد أن الحقل يقبل الأرقام فقط
                                        ->maxLength(15)
                                        ->extraAttributes(['style' => 'text-align: left; direction: ltr;'])
                                        ->tel()
                                        ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'),
                                    // تخصيص عرض حقل الرمز ومحاذاة النص لليسار
                                    // الحد الأقصى لطول الرق,

                                    Forms\Components\TextInput::make('country_code')
                                        ->label('رمز الدولة')
                                        ->placeholder('963')
                                        ->prefix('+')
                                        ->maxLength(3)
                                        ->numeric()
                                        ->extraAttributes(['style' => 'text-align: left; direction: ltr; width: 100px;']),
                                    // تخصيص عرض حقل الرمز ومحاذاة النص لليسار
                                ]),


                                Forms\Components\Textarea::make('address')->label('العنوان التفصيلي'),
                                Forms\Components\Select::make('city_id')->options(City::where('is_main', false)->pluck('name', 'id'))->required()
                                    ->label('البلدة/البلدة')
                                    ->live()->searchable()->preload()
                                    ->reactive()->afterStateUpdated(function ($state, callable $set) {
                                        $set('branch_id', null);
                                        $set('temp', Branch::where('city_id', $state)->pluck('name'));
                                    })->live(),


                                Forms\Components\Radio::make('level')->options(
                                    [
                                        LevelUserEnum::ADMIN->value => LevelUserEnum::ADMIN->getLabel(),
                                        LevelUserEnum::BRANCH->value => LevelUserEnum::BRANCH->getLabel(),
                                        LevelUserEnum::STAFF->value => LevelUserEnum::STAFF->getLabel(),
                                        LevelUserEnum::USER->value => LevelUserEnum::USER->getLabel(),
                                    ]
                                )->default(LevelUserEnum::USER->value)->label('رتبة المستخدم')->required()->live(),


                                Forms\Components\Select::make('branch_id')->label('الفرع')
                                    ->options(fn($get, $context, $record) => Branch::when(
                                        $context == 'create' && $get('level') === LevelUserEnum::BRANCH->value,
                                        fn($query) => $query->whereDoesntHave('users', fn($query) => $query->where('level', LevelUserEnum::BRANCH->value))
                                    )
                                        ->when(
                                            $context == 'edit' && $get('level') === LevelUserEnum::BRANCH->value,
                                            fn($query) => $query
                                                ->whereDoesntHave('users', fn($query) => $query->where('level', LevelUserEnum::BRANCH->value))
                                                ->orWhereHas('users', fn($query) => $query->where('level', LevelUserEnum::BRANCH->value)->where('users.id', $record->id))
                                        )
                                        ->pluck('name', 'id'))->searchable()->visible(fn($get) => $get('level') == LevelUserEnum::STAFF->value || $get('level') == LevelUserEnum::STAFF->value || $get('level') == LevelUserEnum::BRANCH->value)->required(),

                                //                                Forms\Components\TextInput::make('full_name')->label('الاسم الكامل'),
                                Forms\Components\DatePicker::make('birth_date')->label('تاريخ الميلاد')
                                    ->format('Y-m-d')->default(now()),


                                Forms\Components\Select::make('status')->options(
                                    [
                                        ActivateStatusEnum::ACTIVE->value => ActivateStatusEnum::ACTIVE->getLabel(),
                                        ActivateStatusEnum::INACTIVE->value => ActivateStatusEnum::INACTIVE->getLabel(),
                                        ActivateStatusEnum::BLOCK->value => ActivateStatusEnum::BLOCK->getLabel(),

                                    ]
                                )->label('حالة المستخدم')->default('active'),

                                Forms\Components\Select::make('job')->options(
                                    [
                                        JobUserEnum::STAFF->value => JobUserEnum::STAFF->getLabel(),
                                        JobUserEnum::ACCOUNTING->value => JobUserEnum::ACCOUNTING->getLabel(),
                                        JobUserEnum::MANGER->value => JobUserEnum::MANGER->getLabel(),
                                    ]
                                )->label('وظيفة المستخدم'),


                            ]),


                        Tabs\Tab::make('الخارطة')
                            ->schema([
                                Forms\Components\TextInput::make('latitude')->label('خط العرض'),
                                Forms\Components\TextInput::make('longitude')->label('خط الطول'),
                                Map::make('location')
                                    ->label('Location')
                                    ->columnSpanFull()
                                    ->extraStyles([
                                        'min-height: 70vh',
                                        'border-radius: 50px'
                                    ])
                                    ->liveLocation()
                                    ->showMarker()
                                    ->markerColor("#22c55eff")
                                    ->draggable()
                                    ->zoom(15)
                                    ->showMyLocationButton()
                                    ->extraTileControl([])
                                    ->extraControl([
                                        'zoomDelta' => 1,
                                        'zoomSnap' => 2,
                                    ])


                            ]),


                    ]

                )->contained(false)
                    ->columnSpanFull(),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //H: Show Users ID in table cells
                Tables\Columns\TextColumn::make('id')->label('الرقم التسلسلي')->searchable()->sortable(),

                Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable(),
                Tables\Columns\SelectColumn::make('status')->label('حالة المستخدم')
                    ->options([
                        ActivateStatusEnum::ACTIVE->value => ActivateStatusEnum::ACTIVE->getLabel(),
                        ActivateStatusEnum::PENDING->value => ActivateStatusEnum::PENDING->getLabel(),
                        ActivateStatusEnum::BLOCK->value => ActivateStatusEnum::BLOCK->getLabel()

                    ]),
                Tables\Columns\TextColumn::make('level')->badge()
                    ->label('فئة المستخدم')->sortable(),
                Tables\Columns\TextColumn::make('iban')->disabled()->label('IBAN')->copyable(),

                Tables\Columns\TextColumn::make('job')->badge()->label('نوع الموظف'),

                Tables\Columns\TextColumn::make('branch.name')->label('فرع')->sortable(),
                Tables\Columns\TextColumn::make('city.name')->label('المدينة')->sortable(),
                //H: added currency report for users
                Tables\Columns\TextColumn::make('total_balance')->label('الرصيد USD'),
                Tables\Columns\TextColumn::make('pending_balance')->label('الرصيد USD قيد التحصيل'),
                Tables\Columns\TextColumn::make('username')->formatStateUsing(fn($record)=>(double)$record->total_balance+(double)$record->pending_balance)->label('محصلة USD'),

                Tables\Columns\TextColumn::make('total_balance_tr')->label('الرصيد TRY'),
                Tables\Columns\TextColumn::make('total_balance_tr_pending')->label('الرصيد TRYقيد التحصيل'),
     Tables\Columns\TextColumn::make('num_id')->formatStateUsing(fn($record)=>(double)$record->total_balance_tr+(double)$record->total_balance_tr_pending)->label('محصلة TRY'),

            ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('filter')->form([
                    Forms\Components\Select::make('main_city')->options(City::where('is_main',true)->pluck('name','id'))->label('المنطقة')->reactive(),
                    Forms\Components\Select::make('city_id')->options(fn($get)=>City::where('city_id',$get('main_city'))->pluck('name','id'))->label('المدينة')->reactive(),
                    Forms\Components\Select::make('branch_id')->options(fn($get)=>Branch::where('city_id',$get('main_city'))->pluck('name','id'))->label('الفرع'),
                ])->query(fn($query,$data)=>$query
                ->when($data['main_city'],fn($query)=>$query->where('city_id',$data['main_city'])->orWhereHas('city',fn($query)=>$query->where('cities.city_id',$data['main_city'])))
                    ->when($data['city_id'],fn($query)=>$query->where('city_id',$data['city_id']))
                    ->when($data['branch_id'],fn($query)=>$query->where('branch_id',$data['branch_id']))
                )
            ])->headerActions([
                ExportAction::make()->exports([
                    ExcelExport::make()->withChunkSize(100)->fromTable()
                ])
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('balance_usd')->url(fn($record) => UserResource::getUrl('balanceUsd', ['record' => $record]))->label('كشف حساب دولار'),
                    Tables\Actions\Action::make('balance_tr')->url(fn($record) => UserResource::getUrl('balanceTr', ['record' => $record]))->label('كشف حساب تركي'),
                    Tables\Actions\Action::make('balance_usd_pending')->url(fn($record) => UserResource::getUrl('balancePendingUsd', ['record' => $record, 'currency' => 1,'pending'=>1]))->label('كشف حساب دولار قيد التحصيل'),
                    Tables\Actions\Action::make('balance_tr_pending')->url(fn($record) => UserResource::getUrl('balancePendingTR', ['record' => $record, 'currency' => 2,'pending'=>1]))->label('كشف حساب تركي قيد التحصيل'),
Tables\Actions\Action::make('request')->form([
    Forms\Components\Radio::make('currency_id')->options([
        1 => ' من الدولار إلى التركي',
        2 => 'من التركي إلى الدولار',
    ])
        ->label('نوع التحويل')->required()->afterStateUpdated(function ($get,$set) {
            if ($get('currency_id') == 1) {
                $result= HelperBalance::formatNumber((double)$get('amount') * (double)$get('exchange'));
                $set('result',$result);
            } elseif ($get('currency_id') == 2) {
                try{
                    $result=  HelperBalance::formatNumber((double)$get('amount') / (double)$get('exchange'));
                }catch (\Exception| \DivisionByZeroError $e){
                    $result=0;
                }
                $set('result',$result);
            }
        })->live()->debounce(1000),
    Forms\Components\TextInput::make('amount')->label('القيمة')->numeric()->required()->afterStateUpdated(function ($get,$set) {
        if ($get('currency_id') == 1) {
            $result= HelperBalance::formatNumber((double)$get('amount') * (double)$get('exchange'));
            $set('result',$result);
        } elseif ($get('currency_id') == 2) {
            try{
                $result=  HelperBalance::formatNumber((double)$get('amount') / (double)$get('exchange'));
            }catch (\Exception| \DivisionByZeroError $e){
                $result=0;
            }
            $set('result',$result);
        }
    })->live()->debounce(1000),
    Forms\Components\TextInput::make('exchange')->label('سعر التصريف')->numeric()->required()->afterStateUpdated(function ($get,$set) {
        if ($get('currency_id') == 1) {
            $result= HelperBalance::formatNumber((double)$get('amount') * (double)$get('exchange'));
            $set('result',$result);
        } elseif ($get('currency_id') == 2) {
            try{
                $result=  HelperBalance::formatNumber((double)$get('amount') / (double)$get('exchange'));
            }catch (\Exception $e){
                $result=0;
            }
            $set('result',$result);
        }
    })->live()->debounce(1000),
    Forms\Components\TextInput::make('result')->dehydrated(false)->label('الإجمالي')->numeric()->required(),
])->action(function($record,$data){
    if ($data['currency_id'] == 1) {
        $currency=2;
        $result= HelperBalance::formatNumber((double)$data['amount'] * (double)$data['exchange']);

    } elseif ($get('currency_id') == 2) {
        $currency=1;
        try{
            $result=  HelperBalance::formatNumber((double)$data['amount'] / (double)$data['exchange']);
        }catch (\Exception| \DivisionByZeroError $e){
            $result=0;
        }

    }
    $uuid=\Str::uuid();
  \DB::beginTransaction();
  try{
      Balance::create([
          'user_id'=>$record->id,
          'debit'=>$data['amount'],
          'currency_id'=>$data['currency_id'],
          'credit'=>0,
          'is_complete'=>true,
          'pending'=>false,
          'uuid'=>$uuid,
          'info'=>'تصريف عملة من قبل المدير'

      ]);
      Balance::create([
          'user_id'=>$record->id,
          'credit'=>$result,
          'currency_id'=>$currency,
          'debit'=>0,
          'is_complete'=>true,
          'pending'=>false,
          'uuid'=>$uuid,
          'info'=>'تصريف عملة من قبل المدير'

      ]);
      \DB::commit();
      Notification::make('success')->success()->title('نجاح العملية')->body('تم تصريف العملة بنجاح')->send();
  }catch (\Exception |\Error $e){
      \DB::rollBack();
      Notification::make('success')->danger()->title('فشل العملية')->body($e->getMessage())->send();
  }
})->label('تصريف عملة')
                ])->label('كشف حساب'),
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BalancesRelationManager::class,

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
            'balanceTr' => Pages\BalancesTr::route('/{record}/balancesTr'),
            'balanceUsd' => Pages\BalancesUsd::route('/{record}/balancesUsd'),
            'balancePendingUsd' => Pages\BalancesUsd::route('/{record}/balancesPendingUsd'),
            'balancePendingTR' => Pages\BalancesPendingTr::route('/{record}/balancesPendingTr'),
        ];
    }


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
