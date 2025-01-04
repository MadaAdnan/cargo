<?php

namespace App\Providers\Filament;

use App\Filament\Employ\Widgets\AgencyWidget;
use App\Filament\Employ\Widgets\BalanceView;
use App\Filament\Employ\Widgets\TaskCompleteWidget;
use App\Filament\Employ\Widgets\TaskWidget;
use App\Http\Middleware\IsBranchMiddleware;
use App\Http\Middleware\RedirectToEmployMiddleware;
use App\Http\Middleware\RedirectToPanelMiddleware;
use App\Http\Middleware\StopMiddleware;
use App\Http\Middleware\StopPanelMiddleware;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;

class EmployPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('employ')
            ->path('employ')
            ->login()
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->plugins([
                FilamentEditProfilePlugin::make()
                    ->shouldShowDeleteAccountForm(false)
                    ->shouldShowAvatarForm()
                    ->setNavigationLabel('الملف الشخصي')
                    ->setNavigationGroup(' معلومات الحساب')
                    ->setIcon('heroicon-o-user')
                    ->setSort(0)
            ])
            ->maxContentWidth('full')
            ->discoverResources(in: app_path('Filament/Employ/Resources'), for: 'App\\Filament\\Employ\\Resources')
            ->discoverPages(in: app_path('Filament/Employ/Pages'), for: 'App\\Filament\\Employ\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Employ/Widgets'), for: 'App\\Filament\\Employ\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                BalanceView::class,
                AgencyWidget::class,


//                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                StopMiddleware::class,
                StopPanelMiddleware::class
            ])
            ->authMiddleware([
                Authenticate::class,
                IsBranchMiddleware::class,
//                RedirectToEmployMiddleware::class

            ]);
    }
}
