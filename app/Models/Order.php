<?php

namespace App\Models;

use App\Enums\CategoryTypeEnum;
use App\Enums\LevelUserEnum;
use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\BayTypeEnum;
use App\Enums\ActivateStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;


class Order extends Model implements HasMedia
{
    use  InteractsWithMedia;

    protected $casts = [
        'options' => 'array',
        'status' => OrderStatusEnum::class,
        'type' => OrderTypeEnum::class,
        'bay_type' => BayTypeEnum::class


    ];
    protected $guarded = [];


    public function citySource(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_source_id');
    }

    public function cityTarget(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_target_id');
    }

    public function branchSource(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_source_id');
    }

    public function branchTarget(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_target_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id')->where('level',LevelUserEnum::USER->value);
    }

    public function receive(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receive_id')->where('level',LevelUserEnum::USER->value);
    }

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }


    public function fofo(): HasOne
    {
        return $this->hasOne(Package::class);
    }

    public function agencies(): HasMany
    {
        return $this->hasMany(Agency::class);
    }

    public function balances(): HasMany
    {
        return $this->hasMany(Balance::class, 'order_id');
    }


    public function weight(): BelongsTo
    {
        return $this->belongsTo(Category::class)->where('type', CategoryTypeEnum::WEIGHT->value);

    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Category::class)->where('type', CategoryTypeEnum::SIZE->value);

    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function pick(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pick_id');
    }

    public function given(): BelongsTo
    {
        return $this->belongsTo(User::class, 'given_id');
    }
    public function returned(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_id');
    }
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }

}
