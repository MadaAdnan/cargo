<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;
use App\Models\City;

class Area extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }


}
