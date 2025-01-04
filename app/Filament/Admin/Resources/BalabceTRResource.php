<?php

namespace App\Filament\Admin\Resources;

use App\Enums\BalanceTypeEnum;
use App\Filament\Admin\Resources\BalabceTRResource\Pages;
use App\Filament\Admin\Resources\BalabceTRResource\RelationManagers;
use App\Helper\HelperBalance;
use App\Models\BalabceTR;
use App\Models\Balance;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BalabceTRResource extends Resource
{

    protected static ?string $model = Balance::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $pluralModelLabel = 'الرصيد TRY';
    protected static ?string $navigationGroup = 'الرصيد';
    protected static ?string $label = 'الرصيد TRY';
    protected static ?string $navigationLabel = 'الرصيد TRY';

    public static function canCreate(): bool
    {
        return false; // parent::canCreate(); // TODO: Change the autogenerated stub
    }

    public static function canEdit(Model $record): bool
    {
        return parent::canEdit($record) && $record->type === BalanceTypeEnum::START; // TODO: Change the autogenerated stub
    }

    public static function canDelete(Model $record): bool
    {
        return false; //parent::canDelete($record); // TODO: Change the autogenerated stub
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Select::make('user_id')->options(User::active()->get()->mapWithKeys(fn($user) => [$user->id => $user->iban_name]))->searchable()->required()
                        ->label('المستخدم'),
                    TextInput::make('value')->required()->numeric()->label('القيمة'),
                    TextInput::make('info')->label('البيان'),
                    TextInput::make('customer_name')->label('الحساب المقابل'),
                    TextInput::make('created_at')->label('التاريخ'),

                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('credit')->label('دائن')->formatStateUsing(fn($state) => HelperBalance::formatNumber($state)),
                Tables\Columns\TextColumn::make('debit')->label('مدين')->formatStateUsing(fn($state) => HelperBalance::formatNumber($state)),

                Tables\Columns\TextColumn::make('info')->label('الملاحظات'),
                Tables\Columns\TextColumn::make('customer_name')->label('الطرف المقابل'),
                Tables\Columns\TextColumn::make('order.code')->label('الطلب'),
                Tables\Columns\TextColumn::make('order.sender.name')->label('المرسل')->description(fn($record) => $record->order?->general_sender_name != null ? "{$record->order->general_sender_name}" : ""),
                Tables\Columns\TextColumn::make('order.receive.name')->label('المستلم')->description(fn($record) => $record->order?->global_name != null ? " {$record->order->global_name}" : ""),

                Tables\Columns\TextColumn::make('total')->formatStateUsing(fn($state) => HelperBalance::formatNumber($state))->label('الرصيد'),
                //H: get date and time and split them using two temporary columns
                Tables\Columns\TextColumn::make('created_at_date')
                    ->state(function (Model $rec) {
                        return \Carbon\Carbon::parse($rec->created_at)->format('Y-m-d');
                    })
                    ->label('التاريخ'),

                Tables\Columns\TextColumn::make('created_at_time')
                    ->state(function (Model $rec) {
                        return \Carbon\Carbon::parse($rec->created_at)->format('h:i:s');
                    })
                    ->label('التوقيت'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->visible(fn($record) => $record->type === BalanceTypeEnum::START),
                Tables\Actions\Action::make('complete')->action(fn($record) => $record->update(['is_complete' => true]))->visible(fn($record) => !$record->is_complete)
                    ->label('تأكيد إستلام الدفعة')->requiresConfirmation(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBalabceTRS::route('/'),
            'create' => Pages\CreateBalabceTR::route('/create'),
            'edit' => Pages\EditBalabceTR::route('/{record}/edit'),
          'view'=>Pages\ViewBalanceTR::route('/{record}')
        ];
    }
}
