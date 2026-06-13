<?php

namespace Database\Factories;

use App\Domains\Tag\Models\TagModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TagModel>
 */
class TagModelFactory extends Factory
{
    protected $model = TagModel::class;

    public function definition(): array
    {
        return [
            'name'  => fake()->unique()->word(),
            'color' => fake()->hexColor(),
        ];
    }
}
