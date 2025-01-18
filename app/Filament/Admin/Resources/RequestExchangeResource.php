<?php

namespace App\Filament\Admin\Resources;

use App\Enums\OrderStatusEnum;
use App\Filament\Admin\Resources\RequestExchangeResource\Pages;
use App\Filament\Admin\Resources\RequestExchangeResource\RelationManagers;
use App\Helper\HelperBalance;
use App\Models\Exchange;
use App\Models\RequestExchange;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RequestExchangeResource extends Resource
{
    protected static ?string $model = Exchange::class;
    protected static ?string $slug = 'request-exchange';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $pluralModelLabel = 'تصريف العملة';
    protected static ?string $navigationGroup = 'الرصيد';

    protected static ?string $label = 'تصريف العملة';
    protected static ?string $navigationLabel = 'تصريف العملة';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('طلب التصريف')->schema([
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
                            }catch (\Exception $e){
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
                            }catch (\Exception $e){
                                $result=0;
                            }
                            $set('result',$result);
                        }
                    })->live()->default(1)->debounce(1000),
                    Forms\Components\TextInput::make('exchange')->label('سعر التصريف')->numeric()->default(1)->required()->afterStateUpdated(function ($get,$set) {
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

                ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->where('user_id', auth()->id())->latest())
            ->columns([
                Tables\Columns\TextColumn::make('currency_id')->formatStateUsing(function ($state) {
                    $list = [
                        1 => ' من الدولار إلى التركي',
                        2 => 'من التركي إلى الدولار',
                    ];
                    return $list[$state];
                })->label('نوع التحويل'),
                Tables\Columns\TextColumn::make('amount')->label('الكمية')->formatStateUsing(fn($state) => HelperBalance::formatNumber($state)),
                Tables\Columns\TextColumn::make('exchange')->label('سعر الصرف')->formatStateUsing(fn($state) => HelperBalance::formatNumber($state)),
                Tables\Columns\TextColumn::make('status')->formatStateUsing(fn($state) => OrderStatusEnum::tryFrom($state)?->getLabel())->label('الحالة'),
            ])
            ->filters([
                //
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
            'index' => Pages\ListRequestExchanges::route('/'),
            'create' => Pages\CreateRequestExchange::route('/create'),
            'edit' => Pages\EditRequestExchange::route('/{record}/edit'),
        ];
    }
}
