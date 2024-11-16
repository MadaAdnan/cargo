<?php

namespace App\Filament\Admin\Resources;

use App\Enums\CategoryTypeEnum;
use App\Filament\Admin\Resources\CategoryResource\Pages;
use App\Filament\Admin\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $label = 'الفئة';
    protected static ?string $navigationLabel = 'الفئات';
    protected static ?string $pluralModelLabel = 'الفئات';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('الفئات')->schema([
                    Forms\Components\TextInput::make('name')->label('اسم الفئة')->required(),
                    Forms\Components\Radio::make('type')->options([
                        CategoryTypeEnum::SIZE->value => CategoryTypeEnum::SIZE->getLabel(),
                        CategoryTypeEnum::WEIGHT->value => CategoryTypeEnum::WEIGHT->getLabel(),
                    ])->default(CategoryTypeEnum::SIZE->value)->label('نوع الفئة')->visible(fn($context)=>$context !=='edit'),
                    Forms\Components\TextInput::make('internal_price')->numeric()->label('السعر للفئة للشحن الداخلي')->required(),
                    Forms\Components\TextInput::make('external_price')->numeric()->label('السعر للفئة للشحن الخارجي')->required(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->orderBy('type')->orderBy('internal_price'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('اسم الفئة'),
                Tables\Columns\TextColumn::make('type')->label('نوع الفئة'),
                Tables\Columns\TextColumn::make('internal_price')->label('السعر للفئة للشحن الداخلي'),
                Tables\Columns\TextColumn::make('external_price')->label('السعر للفئة للشحن الخارجي'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')->options([
                    CategoryTypeEnum::SIZE->value => CategoryTypeEnum::SIZE->getLabel(),
                    CategoryTypeEnum::WEIGHT->value => CategoryTypeEnum::WEIGHT->getLabel(),
                ])->label('نوع الفئة')
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
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
