<?php

namespace App\Filament\Admin\Resources;

use App\Enums\LevelUserEnum;
use App\Enums\OrderStatusEnum;
use App\Filament\Admin\Resources\ReportResource\Pages;
use App\Filament\Admin\Resources\ReportResource\RelationManagers;
use App\Helper\HelperBalance;
use App\Models\Report;
use App\Models\User;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class ReportResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $pluralModelLabel = 'التقارير';
    protected static ?string $navigationGroup = 'التقارير';

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';


    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->label('الاسم'),
//                Forms\Components\TextInput::make('receivedOrders')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable() //
                ,
                Tables\Columns\TextColumn::make('$numbers')
                    ->label('عدد الطلبات المرسلة')

                    ->getStateUsing(fn($record) => $record->sentOrders->count()),
                Tables\Columns\TextColumn::make('numbers2')
                    ->label('عدد الطلبات المستلمة')
                    ->getStateUsing(fn($record) => $record->receivedOrders->count()),

                Tables\Columns\TextColumn::make('total_balance')
                    ->label('الرصيد الإجمالي')
                    ->formatStateUsing(fn($state)=>HelperBalance::formatNumber($state)),

                Tables\Columns\TextColumn::make('numbers3')
                    ->label('عدد الطلبات المرتجعة ')

                    ->getStateUsing(fn($record) => DB::table('orders')
                        ->where('sender_id', $record->id)
                        ->where('status', '=', OrderStatusEnum::CANCELED->value)
                        ->count())


            ])->paginated(false)
            ->filters([
                Tables\Filters\SelectFilter::make('id')->label('المستخدم')
                    ->options(User::all()->pluck('name', 'id'))

                ,

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('من تاريخ'),
                        Forms\Components\DatePicker::make('created_until')->label('الى تاريخ'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {

                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })


            ])
            ->actions([
                Tables\Actions\ViewAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }
}
