<?php

namespace App\Filament\Admin\Widgets;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class AdminPanelControl extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.admin.widgets.admin-panel-control';
    protected int | string | array $columnSpan = 'full';

    public array $cacheKeys = [
        'branch_panel' => 'لوحة الفروع',
        'user_panel' => 'لوحة المستخدمين',
        'employee_panel' => 'لوحة الموظفين',
    ];

    public array $cacheValues = [];

    public function mount()
    {
        foreach ($this->cacheKeys as $key => $label) {
            $this->cacheValues[$key] = Cache::get($key, false);
        }
        $this->form->fill($this->cacheValues);
    }

    protected function getFormSchema(): array
    {
        $switches = [];
        foreach ($this->cacheKeys as $key => $label) {
            $switches[] = Toggle::make($key)
                ->label($label)
                ->reactive()
                ->afterStateUpdated(fn ($state) => $this->updateCache($key, $state));
        }

        return [
            Grid::make(3)->schema($switches),
        ];
    }

    public function updateCache(string $key, bool $value): void
    {
        Cache::forever($key, $value);

        Notification::make()
            ->title('تم التحديث')
            ->body("{$this->cacheKeys[$key]} تم " . ($value ? 'تفعيلها' : 'تعطيلها') . '.')
            ->success()
            ->send();
    }

    protected function getFormStatePath(): string
    {
        return 'cacheValues';
    }

    public static function canView(): bool
    {
        return Auth::check() && Auth::id() === 54;
    }
}