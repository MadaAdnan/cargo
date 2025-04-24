<?php

namespace App\Filament\Admin\Resources\AccountStatmentResource\Pages;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use App\Filament\Admin\Resources\AccountStatmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Balance;
class EditAccountStatment extends EditRecord
{
    protected static string $resource = AccountStatmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
            ->hidden(fn (Balance $record): bool => $record->order_id !== null),
        ];
    }

    protected function getFormSchema(): array
{
    return [
        Forms\Components\Textarea::make('info')
            ->label('الملاحظات')
            ->columnSpanFull()
            ->visible(fn ($operation): bool => $operation === 'edit') // يظهر فقط في التعديل
    ];
}
}
