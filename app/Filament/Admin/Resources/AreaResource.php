<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AreaResource\Pages;
use App\Filament\Admin\Resources\AreaResource\RelationManagers;
use App\Models\Area;
use App\Models\Branch;
use App\Models\City;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AreaResource extends Resource


{

    protected static ?string $model = City::class;
    protected static ?string $pluralModelLabel = 'البلدات و القرى';
    protected static ?string $label='البلدة';

    protected static ?string $navigationGroup = 'المناطق';

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\TextInput::make('name')->label('البلدة / القرية')->required()->unique(ignoreRecord: true),
                Forms\Components\Select::make('city_id')->options(City::where('is_main',true)->pluck('name','id'))
                ->label('تتبع الى مدينة')->required(),
                Forms\Components\Select::make('branch_id')->options(Branch::all()->pluck('name','id'))
                ->label('تتبع لفرع ')->searchable()->preload()->required(),



            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('name')->label('البلدة / القرية')->searchable(),

                Tables\Columns\TextColumn::make('city.name')->label('تتبع الى مدينة')->searchable(),

                Tables\Columns\TextColumn::make('branch.name')->label('تتبع لفرع '),

                Tables\Columns\TextColumn::make('status')->label('مفعلة/غير مفعلة')





            ])
            ->filters([
                Tables\Filters\SelectFilter::make('city_id')->relationship('city','name')->label('المدينة'),
                Tables\Filters\SelectFilter::make('branch_id')->relationship('branch','name')->label('الفرع')->multiple(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_main', false);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_main',false)->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAreas::route('/'),
            'create' => Pages\CreateArea::route('/create'),
            'edit' => Pages\EditArea::route('/{record}/edit'),
        ];
    }
}
