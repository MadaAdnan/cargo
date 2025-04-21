<?php

namespace App\Filament\Admin\Pages;

use App\Models\Branch;
use App\Models\Order;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Illuminate\Support\Carbon;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard // التوريث الصحيح
{
use HasFiltersForm;



public function filtersForm(Form $form): Form
{
    return $form->schema([
        DatePicker::make('date')
            ->default(now())
            ->reactive()
            ->dehydrated(true),

        Select::make('branch_source_id')
            ->label('الفرع المرسل')
            ->reactive()
            ->searchable()
            ->options(function (callable $get) {
                $date = $get('date') ? Carbon::parse($get('date'))->format('Y-m-d') : null;
                $branchTargetId = $get('branch_target_id');

                if (!$date) return [];

                $query = Order::whereDate('created_at', $date);

                if ($branchTargetId) {
                    $query->where('branch_target_id', $branchTargetId);
                }

                $branchIds = $query->distinct()->pluck('branch_source_id')->filter();

                return Branch::whereIn('id', $branchIds)->pluck('name', 'id');
            }),

        Select::make('branch_target_id')
            ->label('الفرع المستلم')
            ->reactive()
            ->searchable()
            ->options(function (callable $get) {
                $date = $get('date') ? Carbon::parse($get('date'))->format('Y-m-d') : null;
                $branchSourceId = $get('branch_source_id');

                if (!$date) return [];

                $query = Order::whereDate('created_at', $date);

                if ($branchSourceId) {
                    $query->where('branch_source_id', $branchSourceId);
                }

                $branchIds = $query->distinct()->pluck('branch_target_id')->filter();

                return Branch::whereIn('id', $branchIds)->pluck('name', 'id');
            }),
    ])->statePath('filters');
}

}
