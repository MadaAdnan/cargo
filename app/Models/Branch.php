<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\ActivateStatusEnum;

class Branch extends Model
{

    protected $casts=[
        'status'=>ActivateStatusEnum::class
    ];
    use HasFactory;

    protected $guarded=[];


    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function orders(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    public function receivedOrders(): HasMany
    {
        return $this->hasMany(Order::class,'branch_target_id');
    }
    public function sentOrders(): HasMany
    {
        return $this->hasMany(Order::class,'branch_source_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

}
