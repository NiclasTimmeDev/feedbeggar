<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\ProjectSeeder;
use Database\Seeders\FeedbackPostSeeder;
use Database\Seeders\BucketSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Call seeders.
        $this->call([
            UserSeeder::class,
            ProjectSeeder::class,
            FeedbackPostSeeder::class,
            BucketSeeder::class
        ]);
    }
}