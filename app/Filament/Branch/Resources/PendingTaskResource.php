<?php

namespace App\Filament\Branch\Resources;

use App\Enums\LevelUserEnum;
use App\Filament\Branch\Resources\PendingTaskResource\Pages;
use App\Filament\Branch\Resources\PendingTaskResource\RelationManagers;
use App\Models\PendingTask;
use App\Models\Task;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PendingTaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'مهامي بالإنتظار';
    protected static ?string $pluralLabel = 'مهامي بالإنتظار';
    protected static ?string $navigationGroup = 'مهامي';

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
/*
    public static function canAccess(): bool
    {
        return auth()->user()->hasPermissionTo('view_pending::task');

    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermissionTo('create_pending::task'); // TODO: Change the autogenerated stub
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasPermissionTo('update_pending::task');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasPermissionTo('delete_pending::task');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->hasPermissionTo('delete_pending::task');
    }*/

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
            ->modifyQueryUsing(fn($query) => $query->where(fn($query) => $query->where('user_id', auth()->id())->orWhere('delegate_id', auth()->id()))->where('is_pending::taske', false)->latest())
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('التسلسل'),
                Tables\Columns\TextColumn::make('from')->label('إستلام من')->color(fn($record) => $record->is_sender ? 'danger' : null),
                Tables\Columns\TextColumn::make('sender_phone')->label('هاتف المرسل')->url(fn($state) => "https://wa.me/" . trim($state, '+'), true),
                Tables\Columns\TextColumn::make('to')->label('التسليم لـ')->color(fn($record) => $record->is_receive ? 'danger' : null),
                Tables\Columns\TextColumn::make('receive_phone')->label('هاتف المستلم')->url(fn($state) => "https://wa.me/" . trim($state, '+'), true),

                Tables\Columns\TextColumn::make('task')->label('المهمة')->color('danger'),
                Tables\Columns\TextColumn::make('created_at')->since()->label('منذ')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
//                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('complete')->label('إتمام')->button()->requiresConfirmation()->action(fn($record) => $record->update(['is_pending::taske' => true])),
                Tables\Actions\Action::make('transfer')->label('توكيل موظف')->button()
                    ->form([
                        Forms\Components\Select::make('delegate_id')->options(User::where('level', LevelUserEnum::STAFF->value)->orWhere('level', LevelUserEnum::BRANCH->value)->pluck('name', 'id'))->label('الموظف') ->searchable(),
                    ])
                    ->action(function($record, $data) {
                        $record->update(['delegate_id' => $data['delegate_id']]);
                        Notification::make('success')->title('نجاح العملية')->body('تم توكيل الموظف بنجاح')->success()->send();

                    })->visible(fn($record)=>$record->delegate_id===null),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('transfer')->label('توكيل موظف')
                        ->form([
                            Forms\Components\Select::make('delegate_id')->options(User::where('level', LevelUserEnum::STAFF->value)->orWhere('level', LevelUserEnum::BRANCH->value)->pluck('name', 'id'))->label('الموظف')
                            ->searchable(),
                        ])
                        ->action(function($records, $data){
                            Task::whereIn('id', $records->pluck('id')->toArray())->update(['delegate_id' => $data['delegate_id']]);
                            Notification::make('success')->title('نجاح العملية')->body('تم توكيل الموظف بنجاح')->success()->send();
                    }),
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
            'index' => Pages\ListPendingTasks::route('/'),
            'create' => Pages\CreatePendingTask::route('/create'),
            'edit' => Pages\EditPendingTask::route('/{record}/edit'),
        ];
    }
}
