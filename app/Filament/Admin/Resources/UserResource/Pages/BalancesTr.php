<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Helper\HelperBalance;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class BalancesTr extends ManageRelatedRecords
{
    protected static string $resource = UserResource::class;

    protected static string $relationship = 'balancesTr';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return 'رصيد تركي ';
    }
    protected ?string $heading= 'رصيد تركي ';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('credit')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('credit')
            ->columns([
                Tables\Columns\TextColumn::make('credit')->label('دائن')->formatStateUsing(fn($state) => HelperBalance::formatNumber($state)),
                Tables\Columns\TextColumn::make('debit')->label('مدين')->formatStateUsing(fn($state) => HelperBalance::formatNumber($state)),
                Tables\Columns\TextColumn::make('currency.name')->label('العملة'),
                TextColumn::make('id')->label('مدين') ->formatStateUsing(function ($record) {
                    static $runningTotal = 0;
                    $runningTotal += $record->credit - $record->debit;
                    return $runningTotal;
                }),
                Tables\Columns\TextColumn::make('info')->label('الملاحظات'),
                Tables\Columns\TextColumn::make('customer_name')->label('الطرف المقابل'),
                Tables\Columns\TextColumn::make('order.code')->label('الطلب'),
                Tables\Columns\TextColumn::make('order.sender.name')->label('المرسل')->description(fn($record) => $record->order?->general_sender_name != null ? "{$record->order->general_sender_name}" : ""),
                Tables\Columns\TextColumn::make('order.receive.name')->label('المستلم')->description(fn($record) => $record->order?->global_name != null ? " {$record->order->global_name}" : ""),

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
            ->filters([
                //
            ])
            ->headerActions([
//                Tables\Actions\CreateAction::make(),
//                Tables\Actions\AssociateAction::make(),
                ExportAction::make()->exports([
                    ExcelExport::make()->withChunkSize(100)->fromTable()
                ])
            ])
            ->actions([
//                Tables\Actions\ViewAction::make(),
//                Tables\Actions\EditAction::make(),
//                Tables\Actions\DissociateAction::make(),
//                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DissociateBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
