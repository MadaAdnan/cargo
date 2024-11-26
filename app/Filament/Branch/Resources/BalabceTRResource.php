<?php

namespace App\Filament\Branch\Resources;

use App\Filament\Branch\Resources\BalabceTRResource\Pages;
use App\Filament\Branch\Resources\BalabceTRResource\RelationManagers;
use App\Helper\HelperBalance;
use App\Models\BalabceTR;
use App\Models\Balance;
use Filament\Forms;
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

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $pluralModelLabel = 'الرصيد TRY';
    protected static ?string $navigationGroup = 'الرصيد';

    protected static ?string $label = 'الرصيد TRY';
    protected static ?string $navigationLabel = 'الرصيد TRY';
    public static function canCreate(): bool
    {
        return false;// parent::canCreate(); // TODO: Change the autogenerated stub
    }

    public static function canEdit(Model $record): bool
    {
        return false; // TODO: Change the autogenerated stub
    }

    public static function canDelete(Model $record): bool
    {
        return false;//parent::canDelete($record); // TODO: Change the autogenerated stub
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('credit')->label('دائن')->formatStateUsing(fn($state)=>HelperBalance::formatNumber($state)),
                Tables\Columns\TextColumn::make('debit')->label('مدين')->formatStateUsing(fn($state)=>HelperBalance::formatNumber($state)),
                Tables\Columns\TextColumn::make('customer_name')->label('اسم الزبون المستلم'),
                Tables\Columns\TextColumn::make('info')->label('الملاحظات'),
                Tables\Columns\TextColumn::make('customer_name')->label('الطرف المقابل'),
                Tables\Columns\TextColumn::make('order.code')->label('الطلب'),
                Tables\Columns\TextColumn::make('order.sender.name')->label('المرسل')->description(fn($record)=>$record->order?->general_sender_name!=null ? "{$record->order->general_sender_name}":""),
                Tables\Columns\TextColumn::make('order.receive.name')->label('المستلم')->description(fn($record)=>$record->order?->global_name!=null?" {$record->order->global_name}":""),

                Tables\Columns\TextColumn::make('total')->label('الرصيد')->formatStateUsing(fn($state)=>HelperBalance::formatNumber($state)),
                Tables\Columns\TextColumn::make('created_at')->date('Y-m')->label('التاريخ'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('complete')->action(fn($record)=>$record->update(['is_complete'=>true]))->visible(fn($record)=>!$record->is_complete)
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
        ];
    }
}
