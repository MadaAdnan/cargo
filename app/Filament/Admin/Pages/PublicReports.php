<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\BalanceView;
use App\Filament\Admin\Widgets\DailyOverview;
use App\Models\Branch;
use App\Models\Order;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Illuminate\Support\Carbon;

class PublicReports extends Page
{
    use HasFiltersForm;
public static function canAccess(): bool
{
    return auth()->user()->can('page_PublicReports');
}

 protected static string $filterSessionKey = 'PublicReports_filters';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.admin.pages.public-reports';

    protected ?string $heading = 'التقارير';
    protected static ?string $navigationLabel = 'التقارير';
    protected static ?string $navigationGroup = 'التقارير';
protected static ?int $navigationSort = 999;

    protected function getFooterWidgets(): array
    {
        return [
            DailyOverview::class, // تقرير الشحنات اليومي
            BalanceView::class,   // رصيد الصندوق
        ];
    }

    // public function filtersForm(Form $form): Form
    // {
    //     return $form->schema([
    //            DatePicker::make('date')
    //                 ->default(now())
    //                 ->reactive()
    //                 ->dehydrated(true),

    //             Select::make('branch_source_id')
    //                 ->label('الفرع المرسل')
    //                 ->reactive()
    //                 ->searchable()
    //                 ->options(function (callable $get) {
    //                     $date = $get('date') ? Carbon::parse($get('date'))->format('Y-m-d') : null;
    //                     $branchTargetId = $get('branch_target_id');

    //                     if (!$date) return [];

    //                     $query = Order::whereDate('created_at', $date);

    //                     if ($branchTargetId) {
    //                         $query->where('branch_target_id', $branchTargetId);
    //                     }

    //                     $branchIds = $query->distinct()->pluck('branch_source_id')->filter();

    //                     return Branch::whereIn('id', $branchIds)->pluck('name', 'id');
    //                 }),

    //             Select::make('branch_target_id')
    //                 ->label('الفرع المستلم')
    //                 ->reactive()
    //                 ->searchable()
    //                 ->options(function (callable $get) {
    //                     $date = $get('date') ? Carbon::parse($get('date'))->format('Y-m-d') : null;
    //                     $branchSourceId = $get('branch_source_id');

    //                     if (!$date) return [];

    //                     $query = Order::whereDate('created_at', $date);

    //                     if ($branchSourceId) {
    //                         $query->where('branch_source_id', $branchSourceId);
    //                     }

    //                     $branchIds = $query->distinct()->pluck('branch_target_id')->filter();

    //                     return Branch::whereIn('id', $branchIds)->pluck('name', 'id');
    //                 }),

    //     ]);
    // }

      public function filtersForm(Form $form): Form
    {
        return $form->schema([
            DatePicker::make('start_date')
                ->label('تاريخ البداية')
                ->reactive()
                ->default(now()->startOfMonth())
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $endDate = $get('end_date');
                    if ($endDate && Carbon::parse($state)->gt(Carbon::parse($endDate))) {
                        $set('end_date', $state);
                    }
                })
                ->required(),

            DatePicker::make('end_date')
                ->label('تاريخ النهاية')
                ->reactive()
                ->default(now())
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    $startDate = $get('start_date');
                    if ($startDate && Carbon::parse($state)->lt(Carbon::parse($startDate))) {
                        $set('start_date', $state);
                    }
                })
                ->required(),

            Select::make('branch_source_id')
                ->label('الفرع المرسل')
                ->reactive()
                ->searchable()
                ->options(function (callable $get) {
                    $start = $get('start_date');
                    $end = $get('end_date');
                    $target = $get('branch_target_id');

                    if (!$start || !$end) return [];

                    $query = Order::whereBetween('created_at', [Carbon::parse($start)->startOfDay(), Carbon::parse($end)->endOfDay()]);

                    if ($target) {
                        $query->where('branch_target_id', $target);
                    }

                    $branchIds = $query->distinct()->pluck('branch_source_id')->filter();
                    return Branch::whereIn('id', $branchIds)->pluck('name', 'id');
                }),

            Select::make('branch_target_id')
                ->label('الفرع المستلم')
                ->reactive()
                ->searchable()
                ->options(function (callable $get) {
                    $start = $get('start_date');
                    $end = $get('end_date');
                    $source = $get('branch_source_id');

                    if (!$start || !$end) return [];

                    $query = Order::whereBetween('created_at', [Carbon::parse($start)->startOfDay(), Carbon::parse($end)->endOfDay()]);

                    if ($source) {
                        $query->where('branch_source_id', $source);
                    }

                    $branchIds = $query->distinct()->pluck('branch_target_id')->filter();
                    return Branch::whereIn('id', $branchIds)->pluck('name', 'id');
                }),
        ]);
    }
   // Save Filter Parameter In Session PublicReports_Filter
    // public function persistsFiltersInSession(): bool
    // {
    //     return true;
    // }
}
