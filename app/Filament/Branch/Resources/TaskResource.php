<?php

namespace App\Filament\Branch\Resources;

use App\Filament\Branch\Resources\TaskResource\Pages;
use App\Filament\Branch\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $label = 'المهام الإدارية';
    protected static ?string $navigationLabel = 'المهام الإدارية';
    protected static ?string $pluralLabel = 'المهام الإدارية';

    public static function form(Form $form): Form
    {
        $usersList = User::select('name')->pluck('name')->toArray();
        return $form
            ->schema([
                Forms\Components\Section::make('مهام')->schema([
                    Forms\Components\Select::make('user_id')->relationship('user', 'name')->label('المستخدم')->searchable()->required(),
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('from')->label('إستلام من')->datalist($usersList),
                        Forms\Components\TextInput::make('sender_phone')->label('رقم الهاتف'),
                        Forms\Components\Toggle::make('is_sender')->label('صاحب البلاغ')->inline(false),

                    ]),
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('to')->label('التسليم لـ')->datalist($usersList),
                        Forms\Components\TextInput::make('receive_phone')->label('رقم الهاتف'),
                        Forms\Components\Toggle::make('is_receive')->label('صاحب البلاغ')->inline(false),

                    ]),
                    Forms\Components\Textarea::make('task')->label('ملاحظات')
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->where('created_id', auth()->id()))
            ->defaultSort('created_at', 'desc')
            ->poll(10)
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('التسلسل')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('المستخدم')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('from')->label('إستلام من')->color(fn($record)=>$record->is_sender?'danger':null)->sortable(),
                Tables\Columns\TextColumn::make('to')->label('التسليم لـ')->color(fn($record)=>$record->is_receive?'danger':null)->sortable(),
                Tables\Columns\TextColumn::make('task')->label('المهمة'),
                Tables\Columns\TextColumn::make('is_complete')->label('الحالة')->formatStateUsing(fn($state) => $state ? 'تم' : 'بالإنتظار')
                    ->color(fn($state) => $state ? 'success' : 'danger')->sortable()
                ,

                Tables\Columns\TextColumn::make('created_at')->since()->label('منذ')->sortable(),


            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')->relationship('user', 'name')->label('المستخدم')->searchable()->multiple(),
                TernaryFilter::make('is_complete')->label('حالة المهمة')->nullable()
                    ->trueLabel('مكتملة')->falseLabel('بالإنتظار')->placeholder('الكل'),


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
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}