<?php

namespace App\Filament\Admin\Resources;

use App\Enums\OrderStatusEnum;
use App\Filament\Admin\Resources\ExchangeResource\Pages;
use App\Filament\Admin\Resources\ExchangeResource\RelationManagers;
use App\Helper\HelperBalance;
use App\Models\Balance;
use App\Models\Exchange;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExchangeResource extends Resource
{
    protected static ?string $model = Exchange::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $pluralModelLabel = 'طلبات تصريف العملة';
    protected static ?string $navigationGroup = 'الحسابات المالية';

    public static function getNavigationBadge(): ?string
    {
        $count = Exchange::where('status', 'pending')->count();
        return $count; // TODO: Change the autogenerated stub
    }

    protected static ?string $label = 'طلبات تصريف العملة';
    protected static ?string $navigationLabel = 'طلبات تصريف العملة';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('طلب التصريف')->schema([
                    Forms\Components\Radio::make('currency_id')->options([
                        1 => ' من الدولار إلى التركي',
                        2 => 'من التركي إلى الدولار',
                    ])->label('نوع التحويل')->required(),
                    Forms\Components\TextInput::make('amount')->label('القيمة')->numeric()->required(),
                    Forms\Components\TextInput::make('exchange')->label('سعر التصريف')->numeric()->required(),
                ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll(10)
            ->modifyQueryUsing(fn($query) => $query->latest())
            ->columns([
                //H : Added the id of the exchange request
                Tables\Columns\TextColumn::make('id')->label('الرقم التسلسلي')->sortable(),

                Tables\Columns\TextColumn::make('currency_id')->formatStateUsing(function ($state) {
                    $list = [
                        1 => ' من الدولار إلى التركي',
                        2 => 'من التركي إلى الدولار',
                    ];
                    return $list[$state];
                })->label('نوع التحويل'),
                Tables\Columns\TextColumn::make('amount')->label('الكمية'),
                Tables\Columns\TextColumn::make('exchange')->label('سعر الصرف'),
                Tables\Columns\TextColumn::make('created_at')->formatStateUsing(fn($record)=>HelperBalance::formatNumber($record->amount*$record->exchange))->label('قيمة ما سيحصل عليه'),
                Tables\Columns\TextColumn::make('user.name')->label('طلب من'),
                Tables\Columns\TextColumn::make('status')->formatStateUsing(fn($state) => OrderStatusEnum::tryFrom($state)?->getLabel())->label('الحالة'),
            ])
            ->filters([

                Tables\Filters\TernaryFilter::make('status')->trueLabel('بالإنتظار')->falseLabel('تم')
                    ->queries(
                        true: fn($query) => $query->where('status', 'pending'), false: fn($query) => $query->where('status', 'success'), blank: fn($query) => $query
                    )->label('الحالة')
            ])
            ->actions([
                Tables\Actions\EditAction::make()->visible(fn($record) => $record->status === 'pending'),

                Tables\Actions\Action::make('exchange')->action(function ($record) {
                    \DB::beginTransaction();
                    try {
                        $amountTarget = 0;
                        $currencyTarget = 0;
                        if ($record->currency_id == 1) {
//                            if ($record->user->total_balance < $record->amount) {
//                                throw new \Exception('لا يوجد رصيد كاف في الحساب');
//                            }
                            $amountTarget = $record->amount * $record->exchange;
                            $currencyTarget = 2;
                        } else {
//                            if ($record->user->total_balance_tr < $record->amount) {
//                                throw new \Exception('لا يوجد رصيد كاف في الحساب');
//                            }
                            $amountTarget = $record->amount / $record->exchange;
                            $currencyTarget = 1;
                        }
                        Balance::create([
                            'user_id' => $record->user_id,
                            'currency_id' => $record->currency_id,
                            'debit' => $record->amount,
                            'credit' => 0,
                            'ex_cur' => $record->exchange,
                            'is_complete' => 1,
                            'pending' => 0,
                            'info' => 'تحويل من خلال طلب تحويل رقم ' . $record->id,
                        ]);

                        Balance::create([
                            'user_id' => $record->user_id,
                            'currency_id' => $currencyTarget,
                            'debit' => 0,
                            'credit' => $amountTarget,
                            'ex_cur' => $record->exchange,
                            'is_complete' => 1,
                            'pending' => 0,
                            'info' => 'تحويل من خلال طلب تحويل رقم ' . $record->id,
                        ]);
                        $record->update(['status' => OrderStatusEnum::SUCCESS->value]);
                        \DB::commit();
                        Notification::make('success')->title('نجاح العملية')->body('تم التحويل بنجاح')->success()->send();
                    } catch (\Exception | \Error $e) {
                        \DB::rollBack();
                        Notification::make('error')->title('فشل العملية')->body($e->getMessage())->danger()->send();
                    }

                })->label('قبول التحويل')
                    ->requiresConfirmation()->visible(fn($record) => $record->status === 'pending')->button(),
                Tables\Actions\DeleteAction::make()->visible(fn($record) => $record->status === 'pending'),


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
            'index' => Pages\ListExchanges::route('/'),
            'create' => Pages\CreateExchange::route('/create'),
            'edit' => Pages\EditExchange::route('/{record}/edit'),
        ];
    }
}
