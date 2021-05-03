<?php

namespace Database\Seeders;

use App\Models\Bucket;
use Illuminate\Database\Seeder;


class BucketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Bucket::factory()->times(50)->create();
    }
}