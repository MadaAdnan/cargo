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
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Illuminate\Support\Carbon;
class PublicReports extends BaseDashboard
{
   use HasFiltersForm;
protected static string $routePath = 'public-reports';
protected static ?string $title = ' Public Reports';
public static function canAccess(): bool
{
    return auth()->user()->can('page_PublicReports');
}

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    // protected static string $view = 'filament.admin.pages.public-reports'; // هذا يجعلها صفحة عادية بالتالي لا تطبق الفلاتر عليها كما انه يحب عدم تعريف صفحة blade لها

    protected ?string $heading = 'التقارير';
    protected static ?string $navigationLabel = 'التقارير';
    protected static ?string $navigationGroup = 'التقارير';
protected static ?int $navigationSort = 999;

   public function filtersForm (Form $form): Form
    {
        return $form
            ->schema([

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
                ->multiple()
                ->searchable()
                ->options(function (callable $get) {
                    $start = $get('start_date');
                    $end = $get('end_date');
                    $target = $get('branch_target_id');

                    if (!$start || !$end) return [];

                    $query = Order::whereBetween('created_at', [Carbon::parse($start)->startOfDay(), Carbon::parse($end)->endOfDay()]);

                   if ($target && is_array($target)) {
                     $query->whereIn('branch_target_id', $target);
                    } elseif ($target) {
                        $query->where('branch_target_id', $target);
                    }

                    $branchIds = $query->distinct()->pluck('branch_source_id')->filter();
                    return Branch::whereIn('id', $branchIds)->pluck('name', 'id');
                }),

            Select::make('branch_target_id')
                ->label('الفرع المستلم')
                ->reactive()
                ->multiple()
                ->searchable()
                ->options(function (callable $get) {
                    $start = $get('start_date');
                    $end = $get('end_date');
                    $source = $get('branch_source_id');

                    if (!$start || !$end) return [];

                    $query = Order::whereBetween('created_at', [Carbon::parse($start)->startOfDay(), Carbon::parse($end)->endOfDay()]);

                     if ($source && is_array($source)) {
                            $query->whereIn('branch_source_id', $source);
                        } elseif ($source) {
                            $query->where('branch_source_id', $source);
                        }

                    $branchIds = $query->distinct()->pluck('branch_target_id')->filter();
                    return Branch::whereIn('id', $branchIds)->pluck('name', 'id');
                }),


            ])->statePath('filters');

    }
    // Wedgit تظهر بشكل تلقائي لانها من نوع Dashboard
    // protected function getFooterWidgets(): array
    // {
    //     return [
    //         DailyOverview::class, // تقرير الشحنات اليومي
    //         BalanceView::class,   // رصيد الصندوق
    //     ];
    // }




}
