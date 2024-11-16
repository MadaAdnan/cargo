<?php

namespace App\Filament\User\Auth;

use App\Enums\ActivateStatusEnum;
use App\Models\City;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Events\Auth\Registered;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use libphonenumber\PhoneNumberType;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;

class CustomReg extends Register
{
    protected string $userModel;

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended('/user');
        }

        $this->form->fill();
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/register.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/register.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/register.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();
        $data['phone'] =$data['country_code'] . $data['phone_number'];
        unset($data['country_code'], $data['phone_number']); // حذف الحقول المنفصلة بعد الجمع

        $user = $this->getUserModel()::create($data);

        event(new Registered($user));

        $this->sendEmailVerificationNotification($user);

        Filament::auth()->login($user);

        session()->regenerate();

        return app(RegistrationResponse::class);
    }

    protected function sendEmailVerificationNotification(Model $user): void
    {
        if (!$user instanceof MustVerifyEmail) {
            return;
        }

        if ($user->hasVerifiedEmail()) {
            return;
        }

        if (!method_exists($user, 'notify')) {
            $userClass = $user::class;

            throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
        }

        $notification = new VerifyEmail();
        $notification->url = Filament::getVerifyEmailUrl($user);

        $user->notify($notification);
    }


    function form(Form $form): Form
    {
        return $this->makeForm()
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getUserNameFormComponent(),
                $this->getPhoneFormComponent(),
                $this->getCityComponent(),
                $this->getMarketNameComponent(),
                $this->getIdNumComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getAddressFormComponent()
            ])
            ->statePath('data');


    }

    protected function getNameFormComponent(): Component
    {
        return TextInput::make('name')
            ->label(__('filament-panels::pages/auth/register.form.name.label'))
            ->required()
            ->maxLength(255)
            ->autofocus();
    }

    protected function getUserNameFormComponent(): Component
    {
        return TextInput::make('username')
            ->label(__('اسم المستخدم'))
            ->required()->minLength(8)
            ->rule('regex:/^(?=.*[a-zA-Z])(?=.*[0-9])[a-zA-Z0-9]+$/')
            ->helperText('الاسم بالانجليزي حصرا')
            ->maxLength(255);
    }

    protected function getMarketNameComponent(): Component
    {
        return TextInput::make('market_name')
            ->label('اسم المتجر')
            ->maxLength(255)->required();
    }

    protected function getIdNumComponent(): Component
    {
        return TextInput::make('num_id')
            ->label('الرقم الوطني')
            ->maxLength(255);
    }


    protected function getCityComponent(): Component
    {
        return Select::make('city_id')->

        options(
            City::where('status', ActivateStatusEnum::ACTIVE->value)
                ->where('is_main', false)
                ->pluck('name', 'id')
        )
            ->label('البلدة/البلدة')->searchable()->preload()->required();

    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::pages/auth/register.form.email.label'))
            ->email()
            ->required()
            ->maxLength(255)
            ->unique($this->getUserModel());
    }

    protected function getPhoneFormComponent(): Component
    {
        return Grid::make(2) // تقسيم الحقول إلى صفين
        ->schema([

            TextInput::make('phone_number')
                ->label('رقم الهاتف')
                ->placeholder('1234567890')
                ->numeric() // التأكد أن الحقل يقبل الأرقام فقط
                ->maxLength(15)
                ->extraAttributes(['style' => 'text-align: left; direction: ltr;

                '

                ])
                ->tel()
                ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')// تخصيص عرض حقل الرمز ومحاذاة النص لليسار
// الحد الأقصى لطول الرقم
                ->required(),

            TextInput::make('country_code')
                ->label('رمز الدولة')
                ->placeholder('963')->numeric()
                ->prefix('+')
                ->maxLength(3)
                ->extraAttributes(['style' => 'text-align: left; direction: ltr;
                width:120px;
                ']) // تخصيص عرض حقل الرمز ومحاذاة النص لليسار
                // تحديد الحد الأقصى للأرقام (بما في ذلك +)
                ->required(),
        ]);
//            ->helperText('الرجاء ادخال + قبل رقم الهاتف');
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::pages/auth/register.form.password.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->rule('regex:/^(?=.*[a-zA-Z])(?=.*[0-9])[a-zA-Z0-9]+$/')
            ->helperText('يجب أن تحتوي كلمة المرور أحرف و أرقام')
            ->dehydrateStateUsing(fn($state) => Hash::make($state))
            ->same('passwordConfirmation')
            ->validationAttribute(__('filament-panels::pages/auth/register.form.password.validation_attribute'));
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('filament-panels::pages/auth/register.form.password_confirmation.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->dehydrated(false);
    }

    protected function getAddressFormComponent(): Component
    {
        return RichEditor::make('address')
            ->label('العنوان التفصيلي')
            ->required()
            ->autofocus();
    }


    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label(__('filament-panels::pages/auth/register.actions.login.label'))
            ->url(filament()->getLoginUrl());
    }

    protected function getUserModel(): string
    {
        if (isset($this->userModel)) {
            return $this->userModel;
        }

        /** @var SessionGuard $authGuard */
        $authGuard = Filament::auth();

        /** @var EloquentUserProvider $provider */
        $provider = $authGuard->getProvider();

        return $this->userModel = $provider->getModel();
    }

    public function getTitle(): string|Htmlable
    {
        return ('مستخدم جديد');
    }

    public function getHeading(): string|Htmlable
    {
        return ('تسجيل مستخدم جديد');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getRegisterFormAction(),
        ];
    }

    public function getRegisterFormAction(): Action
    {
        return Action::make('register')
            ->label(('تسجيل مستخدم جديد'))
            ->submit('register');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }


}
