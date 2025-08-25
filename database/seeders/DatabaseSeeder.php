<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
{
    $this->call([
       \Database\Seeders\LocationExcelSeeder::class,
    ]);
    $this->call([
    UnitSeeder::class,
]);
}
}
