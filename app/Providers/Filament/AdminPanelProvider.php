<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Widgets\BalanceCustomerView;
use App\Filament\Admin\Widgets\BalanceEmployeeView;
use App\Filament\Admin\Widgets\BalanceView;
use App\Filament\Admin\Widgets\OrdersOverview;
use App\Http\Middleware\RedirectToPanelMiddleware;
use App\Http\Middleware\StopMiddleware;
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
//use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;
use Rupadana\ApiService\ApiServicePlugin;
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->plugins([

                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
               // FilamentApexChartsPlugin::make(),
               // ApiServicePlugin::make(),
                FilamentEditProfilePlugin::make()
                    ->shouldShowDeleteAccountForm(false)
                    ->shouldShowAvatarForm()
                    ->setNavigationLabel('الملف الشخصي')
                    ->setNavigationGroup(' معلومات الحساب')
                    ->setIcon('heroicon-o-user')
                    ->setSort(0)


            ])
            ->login()
            ->colors([
                'primary' => Color::Amber,
                'red' => Color::Red,

            ])
            ->maxContentWidth('full')
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([

                OrdersOverview::class,
                BalanceView::class,
                BalanceEmployeeView::class,
                BalanceCustomerView::class
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
           ->databaseNotifications()
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
                StopMiddleware::class
            ])
            ->authMiddleware([
                Authenticate::class,
//                RedirectToPanelMiddleware::class
            ]);
    }


}
