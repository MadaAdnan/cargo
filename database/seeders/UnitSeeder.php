<?php

namespace Database\Seeders;

use App\Enums\CategoryTypeEnum;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Unit::create([
            'name'=>'كرتونة',

        ]);
        Unit::create([
            'name'=>'طرد',

        ]);

        Category::create([
            'type'=>CategoryTypeEnum::SIZE->value,
            'external_price'=>10,
            'internal_price'=>5,
            'name'=>'10  متر'
        ]);
        Category::create([
            'type'=>CategoryTypeEnum::WEIGHT->value,
            'external_price'=>10,
            'internal_price'=>5,
            'name'=>'10  كغ'
        ]);
    }
}
