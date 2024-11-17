<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AccountStatmentResource\Pages;
use App\Filament\Admin\Resources\AccountStatmentResource\RelationManagers;
use App\Models\AccountStatment;
use App\Models\Balance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountStatmentResource extends Resource
{
    protected static ?string $model = Balance::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationGroup = 'التقارير';

    //H: changed page label
    protected static ?string $pluralModelLabel = 'كشف حساب';
    protected static ?string $label = 'كشف حساب';
    protected static ?string $navigationLabel = 'كشف حساب';
    public static function canCreate(): bool
    {
        return false; // TODO: Change the autogenerated stub
    }

    public static function canEdit(Model $record): bool
    {
        return false; // TODO: Change the autogenerated stub
    }


    public static function canDelete(Model $record): bool
    {
        return false; // TODO: Change the autogenerated stub
    }

    public static function canDeleteAny(): bool
    {
        return false; // TODO: Change the autogenerated stub
    }

    public static function canForceDelete(Model $record): bool
    {
        return false; // TODO: Change the autogenerated stub
    }

    public static function canForceDeleteAny(): bool
    {
        return false; // TODO: Change the autogenerated stub
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
            ->poll(10)
            ->columns([
                
                Tables\Columns\TextColumn::make('credit')->label('إيداع'),
                Tables\Columns\TextColumn::make('debit')->label('قبض'),

                /*Tables\Columns\BadgeColumn::make('process_type')
                    ->label('نوع العملية')
                    ->getStateUsing(function ($record) {
                        if ($record->credit == 0) {
                            return 'قبض';
                        } elseif (
                            $record->debit == 0
                        ) {
                            return 'إيداع';
                        }
                        return 'غير معروف';
                    })
                    ->colors([
                        'success' => 'إيداع',
                        'danger' => 'قبض',
                        'gray' => 'غير معروف',
                    ]),*/


                /*Tables\Columns\TextColumn::make('amount')->label('المبلغ')
                    ->getStateUsing(function ($record) {
                        if ($record->credit == 0) {
                            return $record->debit;
                        } elseif ($record->debit == 0) {
                            return $record->credit;
                        }
                        return 'غير معروف';
                    }),*/

                Tables\Columns\TextColumn::make('info')->label('الملاحظات'),
                Tables\Columns\TextColumn::make('customer_name')->label('الطرف المقابل'),
                Tables\Columns\TextColumn::make('order.code')->label('الطلب'),
                Tables\Columns\TextColumn::make('order.sender.name')->label('المرسل')->description(fn($record) => $record->order?->general_sender_name != null ? "{$record->order->general_sender_name}" : ""),
                Tables\Columns\TextColumn::make('order.receive.name')->label('المستلم')->description(fn($record) => $record->order?->global_name != null ? " {$record->order->global_name}" : ""),
                
                //H: disabled the cell
                //Tables\Columns\TextColumn::make('total')->label('الرصيد'),

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

            ])->defaultSort('id', 'desc')
            //H: up here, added default sorting to table based on id to show the latest total of an account 
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')->relationship('user', 'name')->searchable()->default(0)->label('المستخدم'),
                Tables\Filters\SelectFilter::make('currency_id')->options([
                    1 => 'دولار',
                    2 => 'تركي'
                ])->default(1)->label('العملة'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

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
            'index' => Pages\ListAccountStatments::route('/'),
            'create' => Pages\CreateAccountStatment::route('/create'),
            'edit' => Pages\EditAccountStatment::route('/{record}/edit'),
        ];
    }
}