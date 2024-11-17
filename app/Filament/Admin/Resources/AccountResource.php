<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AccountResource\Pages;
use App\Filament\Admin\Resources\AccountResource\RelationManagers;
use App\Helper\HelperBalance;
use App\Models\Account;
use App\Models\Balance;
use App\Models\Branch;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $slug = 'accounts';
    protected static ?string $navigationGroup = 'الحسابات المالية';
    protected static ?string $label = 'الحسابات المالية';
    protected static ?string $pluralLabel = 'الحسابات المالية';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('الحسابات المالية')->schema([
                    Forms\Components\TextInput::make('name')->label('اسم الحساب')->required(),

                    Forms\Components\TextInput::make('iban')->label('كود الحساب')->required()->unique(ignoreRecord: true)->dehydrated(fn($context) => $context === 'create')->default(HelperBalance::getMaxCodeAccount()),
                   Forms\Components\Select::make('currency_id')->relationship('currency','name')->required()->label('عملة الحساب')->dehydrated(fn($context)=>$context=='create'),
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
                ])->fillForm(fn($record) => ['name' => $record->name, 'branch_id' => $record->branch_id])
                    ->action(function($record, $data){
                        $record->update(['name' => $data['name'], 'branch_id' => $data['branch_id']]);
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