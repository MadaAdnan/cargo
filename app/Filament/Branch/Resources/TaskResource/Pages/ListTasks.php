<?php

namespace App\Filament\Branch\Resources\TaskResource\Pages;

use App\Filament\Branch\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        return [
            Tab::make('all')->modifyQueryUsing(fn($query)=>$query->where('created_id', auth()->id()))->label('الكل'),
            Tab::make('pending')->modifyQueryUsing(fn($query)=>$query->where('created_id', auth()->id())->where('is_complete',false))->label('بالإنتظار'),
            Tab::make('pending')->modifyQueryUsing(fn($query)=>$query->where('created_id', auth()->id())->where('is_complete',true))->label('مكتملة'),
        ];
    }
}
