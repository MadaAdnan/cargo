<?php

namespace Database\Seeders;

use App\Enums\ActivateStatusEnum;
use App\Models\Branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::create([
            'name' => 'فرع الدانا',
            'address' => 'الدانا الشارع الرئيسي',
            'status' => ActivateStatusEnum::ACTIVE->value,
            'city_id'=>1,

        ]);

        Branch::create([
            'name' => 'فرع سرمدا',
            'address' => 'سرمدا الشارع الرئيسي',
            'status' => ActivateStatusEnum::ACTIVE->value,
            'city_id'=>1,

        ]);
    }
}
