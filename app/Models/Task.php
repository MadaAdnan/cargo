<?php

namespace App\Models;

use App\Enums\LevelUserEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->where('level',LevelUserEnum::STAFF->value)->orWhere('level',LevelUserEnum::BRANCH->value);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_id');
    }
    public function delegate(): BelongsTo
    {
        return $this->belongsTo(User::class,'delegate_id');
    }
}
