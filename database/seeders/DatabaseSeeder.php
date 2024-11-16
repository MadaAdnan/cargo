<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Enums\LevelUserEnum;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         \App\Models\User::factory(30)->create();

         \App\Models\User::create([
             'name' => 'Test User',
             'email' => 'admin@admin.com',
             'password'=>bcrypt('password'),
             'level'=>LevelUserEnum::ADMIN->value,
             'username' => 'admin',
         ]);
         $this->call(CitySeeder::class);
         $this->call(BranchSeeder::class);
         $this->call(UnitSeeder::class);
    }
}
