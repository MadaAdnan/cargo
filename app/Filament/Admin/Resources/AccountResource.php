<?php

namespace App\Filament\Admin\Resources;

use App\Enums\TypeAccountEnum;
use App\Filament\Admin\Resources\AccountResource\Pages;
use App\Filament\Admin\Resources\AccountResource\RelationManagers;
use App\Helper\HelperBalance;
use App\Models\Account;
use App\Models\Balance;
use App\Models\Branch;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountResource extends Resource implements HasShieldPermissions
{
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

    protected static ?string $model = User::class;
    protected static ?string $slug = 'accounts';
    protected static ?string $navigationGroup = 'الحسابات المالية';
    protected static ?string $label = 'الحسابات المالية';
    protected static ?string $pluralLabel = 'الحسابات المالية';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasPermissionTo('view_account');
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasPermissionTo('view_account'); // TODO: Change the autogenerated stub
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermissionTo('create_account'); // TODO: Change the autogenerated stub
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasPermissionTo('update_account');  // TODO: Change the autogenerated stub
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasPermissionTo('delete_account');  // TODO: Change the autogenerated stub
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('الحسابات المالية')->schema([
                    Forms\Components\TextInput::make('name')->label('اسم الحساب')->required(),

                    Forms\Components\TextInput::make('iban')->label('كود الحساب')->required()->unique(ignoreRecord: true)->dehydrated(fn($context) => $context === 'create')->default(HelperBalance::getMaxCodeAccount()),
                    Forms\Components\Select::make('type_account')
                        ->options([
                            TypeAccountEnum::OIL->value=>TypeAccountEnum::OIL->getLabel(),
                            TypeAccountEnum::OFFICE->value=>TypeAccountEnum::OFFICE->getLabel(),
                            TypeAccountEnum::SENDER->value=>TypeAccountEnum::SENDER->getLabel(),
                            TypeAccountEnum::TOOL->value=>TypeAccountEnum::TOOL->getLabel(),
                            TypeAccountEnum::TRANSFER->value=>TypeAccountEnum::TRANSFER->getLabel(),
                            TypeAccountEnum::ANY->value=>TypeAccountEnum::ANY->getLabel(),
                            TypeAccountEnum::STAFF->value=>TypeAccountEnum::STAFF->getLabel(),
                            TypeAccountEnum::BALANCE->value=>TypeAccountEnum::BALANCE->getLabel(),
                            TypeAccountEnum::WITHDRAW->value=>TypeAccountEnum::WITHDRAW->getLabel(),
                        ])->label('نوع الحساب')->required(),
                    Forms\Components\Select::make('currency_id')->relationship('currency', 'name')->required()->label('عملة الحساب')->dehydrated(fn($context) => $context == 'create'),
                    Forms\Components\Select::make('branch_id')->options(Branch::pluck('name', 'id'))->label('الفرع'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->accounts())
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('اسم الحساب')->searchable(),
                Tables\Columns\TextColumn::make('iban')->label('كود الحساب')->searchable(),
                Tables\Columns\TextColumn::make('type_account')->label('نوع الحساب'),
                Tables\Columns\TextColumn::make('currency.name')->label('عملة الحساب')->searchable(),
                Tables\Columns\TextColumn::make('branch.name')->label('الفرع')->searchable(),
                Tables\Columns\TextColumn::make('total_balance')->label('الرصيد'),

            ])
            ->filters([
                //
            ])
            ->actions([
//                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('edit_record')->form([
                    Forms\Components\TextInput::make('name')->label('اسم الحساب')->required(),
                    Forms\Components\Select::make('branch_id')->options(Branch::pluck('name', 'id'))->label('الفرع')->searchable(),
                    Forms\Components\Select::make('type_account')
                        ->options([
                            TypeAccountEnum::OIL->value=>TypeAccountEnum::OIL->getLabel(),
                            TypeAccountEnum::OFFICE->value=>TypeAccountEnum::OFFICE->getLabel(),
                            TypeAccountEnum::SENDER->value=>TypeAccountEnum::SENDER->getLabel(),
                            TypeAccountEnum::TOOL->value=>TypeAccountEnum::TOOL->getLabel(),
                            TypeAccountEnum::TRANSFER->value=>TypeAccountEnum::TRANSFER->getLabel(),
                            TypeAccountEnum::ANY->value=>TypeAccountEnum::ANY->getLabel(),
                            TypeAccountEnum::STAFF->value=>TypeAccountEnum::STAFF->getLabel(),
                            TypeAccountEnum::BALANCE->value=>TypeAccountEnum::BALANCE->getLabel(),
                            TypeAccountEnum::WITHDRAW->value=>TypeAccountEnum::WITHDRAW->getLabel(),
                        ])->label('نوع الحساب')->required(),

                ])->fillForm(fn($record) => ['name' => $record->name, 'branch_id' => $record->branch_id])
                    ->action(function ($record, $data) {
                        $record->update(['name' => $data['name'], 'branch_id' => $data['branch_id'],'type_account'=>$data['type_account']]);
                        Notification::make('success')->title('نجاح العملية')->body('تم التعديل بنجاح')->success()->send();
                    })->label('تعديل'),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }
}
