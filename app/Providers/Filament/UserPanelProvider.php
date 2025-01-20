<?php

namespace App\Providers\Filament;

use App\Filament\User\Auth\CustomLogin;
use App\Filament\User\Auth\CustomReg;
use App\Http\Middleware\IsBlockedUserMiddleware;
use App\Http\Middleware\RedirectToUserMiddleware;
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

class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('user')
            ->path('user')
            ->login(CustomLogin::class)
            ->passwordReset()
            ->registration(CustomReg::class)
            ->profile()
            ->colors([
                'primary' => Color::Emerald,
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
            ->discoverResources(in: app_path('Filament/User/Resources'), for: 'App\\Filament\\User\\Resources')
            ->discoverPages(in: app_path('Filament/User/Pages'), for: 'App\\Filament\\User\\Pages')
            ->pages([
//                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/User/Widgets'), for: 'App\\Filament\\User\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,

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
                StopPanelMiddleware::class
            ])
            ->authMiddleware([
                Authenticate::class,
                IsBlockedUserMiddleware::class,
//                RedirectToUserMiddleware::class
            ])
            ->databaseNotifications();
        ;
    }
}
