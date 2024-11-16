<?php

namespace Database\Seeders;

use App\Enums\ActivateStatusEnum;
use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        City::create([
            'name'=>'إدلب',
            'is_main'=>true,
            'status'=>ActivateStatusEnum::ACTIVE->value,
        ]);

        City::create([
            'name'=>'بنش',
            'is_main'=>false,
            'city_id'=>1,
            'status'=>ActivateStatusEnum::ACTIVE->value,
        ]);
        City::create([
            'name'=>'سرمدا',
            'is_main'=>false,
            'city_id'=>1,
            'status'=>ActivateStatusEnum::ACTIVE->value,
        ]);
    }
}
