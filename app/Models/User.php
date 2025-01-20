<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\TypeAccountEnum;
use App\Helper\HelperBalance;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Filament\Models\Contracts\HasAvatar;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use App\Enums\LevelUserEnum;
use App\Enums\JobUserEnum;
use App\Enums\ActivateStatusEnum;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, FilamentUser, HasAvatar
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, InteractsWithMedia;
    use HasPanelShield;


    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin' && $this->level == LevelUserEnum::ADMIN) {

            return true;
        } elseif ($panel->getId() === 'branch' && $this->level == LevelUserEnum::BRANCH) {
            return true;
        } elseif ($panel->getId() === 'employ' && ($this->level === LevelUserEnum::STAFF)) {
            return true;
        } elseif ($panel->getId() === 'user' && $this->level === LevelUserEnum::USER) {
            return true;
        }
        return false;

    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url("$this->avatar_url") : null;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected
        $guarded = [];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected
        $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected
        $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'level' => LevelUserEnum::class,
        'status' => ActivateStatusEnum::class,
        'job' => JobUserEnum::class,
        'type_account' => TypeAccountEnum::class,
        'location' => 'array'

    ];

    protected static function booted()
    {
        parent::booted(); // TODO: Change the autogenerated stub
        static::addGlobalScope('userOnly', fn($query) => $query->where('is_account', false));


    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function sentOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'sender_id');
    }

    public function receivedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'receive_id');
    }

    public function balances(): HasMany
    {
        return $this->hasMany(Balance::class)->where('balances.is_complete', 1)->where('pending', '!=', true);
    }
    public function balancesTr(): HasMany
    {
        return $this->balances()->where('currency_id',2);
    }
    public function balancesUsd(): HasMany
    {
        return $this->balances()->where('currency_id',1);
    }

    public function balancesPendingTr(): HasMany
    {
        return  $this->hasMany(Balance::class)->where('balances.is_complete', 1)->where('pending', '=', true)->where('currency_id',2);
    }
    public function balancesPendingUsd(): HasMany
    {
        return $this->hasMany(Balance::class)->where('balances.is_complete', 1)->where('pending', '=', true)->where('currency_id',1);
    }

    public function pendingBalances(): HasMany
    {
        return $this->hasMany(Balance::class)->where('balances.pending', 1);
    }

    public function getTotalBalanceAttribute(): float
    {
        $total = DB::table('balances')->where('user_id', $this->id)->where('is_complete', true)
                ->where('currency_id',1)
                ->where('pending', '!=', true)
                ->selectRaw('SUM(credit) - SUM(debit) as total')->first()?->total ?? 0;
        return  HelperBalance::formatNumber($total);
    }
    public function getPendingBalanceAttribute(): float
    {
        $total = DB::table('balances')
                ->where('user_id', $this->id)
                ->where('currency_id', 1)
                ->where('pending', true)
                ->selectRaw('SUM(credit) - SUM(debit) as total')->first()?->total ?? 0;
        return  HelperBalance::formatNumber($total);
    }

    public function getTotalBalanceTrAttribute(): float
    {
        $total = DB::table('balances')->where('user_id', $this->id)->where('is_complete', true)
                ->where('currency_id',2)
                ->where('pending', '!=', true)
                ->selectRaw('SUM(credit) - SUM(debit) as total')->first()?->total ?? 0;
        return  HelperBalance::formatNumber($total);
    }


    public function getTotalBalanceTrPendingAttribute(): float
    {
        $total = DB::table('balances')->where('user_id', $this->id)->where('is_complete', true)
            ->where('currency_id', 2)
            ->where('pending', true)
            ->selectRaw('SUM(credit) - SUM(debit) as total')->first()?->total ?? 0;
        return  HelperBalance::formatNumber($total);
    }



    public function getIbanNameAttribute(): string
    {
        return $this->iban . ' - ' . $this->name;
    }

    public function scopeAccounts($query)
    {
        return $query->withoutGlobalScope('userOnly')->where('is_account', true);
    }

    public function scopeActive($query)
    {
        return $query->withoutGlobalScope('userOnly')->whereNot('status', ActivateStatusEnum::BLOCK->value);
    }

    public function scopeHideGlobal( $query)
    {
        return $query->whereNotIn('id', [89,76])->whereNot('status',ActivateStatusEnum::BLOCK->value);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
    public function scopeWithAccount($query)
    {
        return $query->withoutGlobalScope('userOnly');
    }

}
