<?php

namespace App\Filament\Admin\Resources;

use App\Enums\BalanceTypeEnum;
use App\Filament\Admin\Resources\BalanceResource\Pages;
use App\Filament\Admin\Resources\BalanceResource\RelationManagers;
use App\Helper\HelperBalance;
use App\Models\Balance;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Actions\ExportAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class BalanceResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Balance::class;

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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $pluralModelLabel = 'الرصيد USD';
    protected static ?string $navigationGroup = 'الرصيد';
    protected static ?string $label = 'الرصيد USD';
    protected static ?string $navigationLabel = 'الرصيد USD';

    public static function canAccess(): bool
    {
        return auth()->user()->hasPermissionTo('view_balance');

    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermissionTo('view_balance'); // TODO: Change the autogenerated stub
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasPermissionTo('update_balance');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasPermissionTo('delete_balance');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->hasPermissionTo('delete_balance');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('credit')->label('مدين')->formatStateUsing(fn($state) => HelperBalance::formatNumber($state)),
                Tables\Columns\TextColumn::make('debit')->label('دائن')->formatStateUsing(fn($state) => HelperBalance::formatNumber($state)),

                Tables\Columns\TextColumn::make('info')->label('الملاحظات'),
                Tables\Columns\TextColumn::make('customer_name')->label('الطرف المقابل'),
                Tables\Columns\TextColumn::make('order.code')->label('الطلب'),
                Tables\Columns\TextColumn::make('order.sender.name')->label('المرسل')->description(fn($record) => $record->order?->general_sender_name != null ? "{$record->order->general_sender_name}" : ""),
                Tables\Columns\TextColumn::make('order.receive.name')->label('المستلم')->description(fn($record) => $record->order?->global_name != null ? " {$record->order->global_name}" : ""),

                Tables\Columns\TextColumn::make('total')->label('الرصيد')->formatStateUsing(fn($state) => HelperBalance::formatNumber($state)),

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
            ->headerActions([
                \pxlrbt\FilamentExcel\Actions\Tables\ExportAction::make()->exports([
                    ExcelExport::make()->withChunkSize(100)->fromTable()
                ])
            ])
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
            'index' => Pages\ListBalances::route('/'),
            'create' => Pages\CreateBalance::route('/create'),
            'edit' => Pages\EditBalance::route('/{record}/edit'),
            'view' => Pages\ViewBalance::route('/{record}'),
        ];
    }
}
