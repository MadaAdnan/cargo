<?php

namespace App\Filament\Branch\Resources;

use App\Enums\ActivateStatusEnum;
use App\Enums\BalanceTypeEnum;
use App\Enums\JobUserEnum;
use App\Enums\LevelUserEnum;
use App\Filament\Branch\Resources\UserResource\Pages;
use App\Filament\Branch\Resources\UserResource\RelationManagers;
use App\Models\Balance;
use App\Models\Branch;
use App\Models\City;
use App\Models\User;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Exception;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $pluralModelLabel = 'المستخدمون';

    protected static ?string $label = 'مستخدم';
    protected static ?string $navigationLabel = 'المستخدمون';
    protected static ?string $navigationIcon = 'heroicon-o-users';


    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();

        // التحقق من أن المستخدم المسجل في نفس الفرع
        return $user->branch_id === $record->branch_id;
    }

    public static function form(Form $form): Form
    {
        $max=User::max('id')+1;


        return $form
            ->schema([
                Forms\Components\CheckboxList::make('roles')->columnSpanFull()
                    ->relationship('roles', 'name',fn($query)=>$query->where('name','super_admin'))->label('الصلاحيات'),
                Forms\Components\TextInput::make('name')->label('الاسم')->required(),
                Forms\Components\TextInput::make('email')->label('البريد الالكتروني')->email()->required()->unique(ignoreRecord: true) ->default('user'. $max.'@gmail.com'),
                Forms\Components\TextInput::make('username')->label('username')
                    ->unique(ignoreRecord: true)->required() ->default('user'. $max),
                Forms\Components\TextInput::make('password')->password()->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))->label('كلمة المرور')->revealable()->default(12345),


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

                    Forms\Components\TextInput::make('country_code')
                        ->label('رمز الدولة')
                        ->placeholder('963')
                        ->prefix('+')
                        ->maxLength(3)
                        ->numeric()
                        ->extraAttributes(['style' => 'text-align: left; direction: ltr; width: 100px;']) ,
                ]),


                Forms\Components\Textarea::make('address')->label('العنوان التفصيلي'),
                Forms\Components\Select::make('city_id')->options(City::where('is_main', false)->pluck
                ('name', 'id'))->required()
                    ->label('المدينة/البلدة')
                    ->live()->searchable()->preload()
                    ->reactive()->afterStateUpdated(function ($state, callable $set) {
                        $set('branch_id', null);
                        $set('temp', Branch::where('city_id', $state)->pluck('name'));
                    })->live(),

                Forms\Components\Radio::make('level')->options(
                    [
//                        LevelUserEnum::ADMIN->value => LevelUserEnum::ADMIN->getLabel(),
//                        LevelUserEnum::BRANCH->value => LevelUserEnum::BRANCH->getLabel(),
                        LevelUserEnum::STAFF->value => LevelUserEnum::STAFF->getLabel(),
                        LevelUserEnum::USER->value => LevelUserEnum::USER->getLabel(),
                    ]
                )->default(LevelUserEnum::USER->value)->label('رتبة المستخدم')->required()->live(),
                Forms\Components\Select::make('branch_id')->label('الفرع')


                    ->options(fn($get,$context,$record)=> Branch::when($context=='create' && $get('level')===LevelUserEnum::BRANCH->value,
                        fn($query)=>$query->whereDoesntHave('users',fn($query)=>$query->where('level',LevelUserEnum::BRANCH->value)))
                        ->when($context=='edit' && $get('level')===LevelUserEnum::BRANCH->value,
                            fn($query)=>$query
                                ->whereDoesntHave('users',fn($query)=>$query->where('level',LevelUserEnum::BRANCH->value))
                                ->orWhereHas('users',fn($query)=>$query->where('level',LevelUserEnum::BRANCH->value)->where('users.id',$record->id)))
                        ->pluck('name','id'))->searchable()->visible(fn($get)=>$get('level')==LevelUserEnum::STAFF->value || $get('level')==LevelUserEnum::STAFF->value || $get('level')==LevelUserEnum::BRANCH->value)->required(),

//                Forms\Components\TextInput::make('full_name')->label('الاسم الكامل'),
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








            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable(),
                Tables\Columns\SelectColumn::make('status')->label('حالة المستخدم')
                    ->options([
                        ActivateStatusEnum::ACTIVE->value=>ActivateStatusEnum::ACTIVE->getLabel(),
                        ActivateStatusEnum::PENDING->value=>ActivateStatusEnum::PENDING->getLabel(),
                        ActivateStatusEnum::BLOCK->value=>ActivateStatusEnum::BLOCK->getLabel()

                    ]),
                Tables\Columns\TextColumn::make('level')->badge()
                    ->label('فئة المستخدم')->sortable(),
                Tables\Columns\TextColumn::make('iban')->disabled()->label('IBAN')->copyable(),

                Tables\Columns\TextColumn::make('job')->badge()->label('نوع الموظف'),

                Tables\Columns\TextColumn::make('branch.name')->label('فرع')->sortable(),
                Tables\Columns\TextColumn::make('city.name')->label('المدينة')->sortable(),


            ])->defaultSort('created_at', 'desc')
            ->filters([

            ])
            ->actions([

                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make(),
//                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('credit_balance')->label('اضافة رصيد')->form([
                    Forms\Components\TextInput::make('credit')
                        ->required()
                        ->minValue(0.1)->label('القيمة'),
                    Forms\Components\TextInput::make('info')->label('ملاحظات')->required(),

                ])->action(function ($record, $data) {

                    if ($data['credit'] > 0) {
                        \DB::beginTransaction();
                        try {
                            Balance::create([
                                'user_id' => $record->id,
                                'credit' => $data['credit'],
                                'debit' => 0,
                                'is_complete' => true,
                                'info' => $data['info'],
                                'type' => BalanceTypeEnum::PUSH->value,
                                'total' => $record->total_balance + $data['credit'],
                            ]);
                            Balance::create([
                                'user_id' => auth()->id(),
                                'credit' => 0,
                                'debit' => $data['credit'],
                                'is_complete' => true,
                                'info' => "شحن رصيد للمستخدم {$record->full_name}",
                                'type' => BalanceTypeEnum::CATCH->value,
                                'total' => auth()->user()->total_balance - $data['credit'],
                            ]);
                            \DB::commit();
                            Notification::make('success')->success()->title('نجاح العملية')->body("تم إضافة رصيد إلى المستخدم {$record->full_name}")->send();

                        } catch (Exception | \Error $e) {
                            \DB::rollBack();
                            Notification::make('success')->danger()->title('فشل العملية')->body($e->getMessage())->send();

                        }


                    }


                })->label('إضافة رصيد')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
