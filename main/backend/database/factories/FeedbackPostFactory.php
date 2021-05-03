<?php

namespace Database\Factories;

use App\Models\FeedbackPost;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Bucket;
use App\Models\Project;

class FeedbackPostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FeedbackPost::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'project_id' => Project::all()->random()->id,
            'type' => 'idea',
            'email' => $this->faker->unique()->safeEmail,
            'text' => $this->faker->text,
            'browser_name' => 'Chrome',
            'browser_version' => '10',
            'os_name' => 'MacOS',
            'is_archived' => 0,
        ];
    }
}