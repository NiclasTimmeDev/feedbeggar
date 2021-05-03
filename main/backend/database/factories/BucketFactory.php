<?php

namespace Database\Factories;

use App\Models\Bucket;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class BucketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Bucket::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'project_id' => Project::all()->random()->id,
            'name' => $this->faker->name,
            'description' => $this->faker->text,
        ];
    }
}