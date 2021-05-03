<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeedbackPost;

class FeedbackPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        FeedbackPost::factory()->times(100)->create();
    }
}