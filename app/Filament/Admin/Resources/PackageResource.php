<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PackageResource\Pages;
use App\Filament\Admin\Resources\PackageResource\RelationManagers;
use App\Filament\Admin\Resources\OrderResource;
use App\Models\Package;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Enums\OrderTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\BayTypeEnum;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;
    protected static ?string $pluralModelLabel = 'الشحنات';

    protected static ?string $label='شحنة';
    protected static ?string $navigationLabel='الشحنات';
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([


                Forms\Components\Select::make('order_id')
                ->relationship('order','code')
                ->createOptionForm(fn(Form $form)=>OrderResource::form($form))






            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
    public static function canViewAny(): bool
    {
        return false;
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPackages::route('/'),
            'create' => Pages\CreatePackage::route('/create'),
            'edit' => Pages\EditPackage::route('/{record}/edit'),
        ];
    }
}
