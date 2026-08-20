<?php

namespace Database\Factories;

use App\Domains\Annotation\Models\AnnotationModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnnotationModel>
 */
class AnnotationModelFactory extends Factory
{
    protected $model = AnnotationModel::class;

    public function definition(): array
    {
        $snapshot = fake()->sentence();

        return [
            'content'       => fake()->paragraph(),
            'text_snapshot' => $snapshot,
            'anchor'        => [
                'version'   => 1,
                'line'      => fake()->numberBetween(1, 200),
                'tag'       => fake()->randomElement(['p', 'h1', 'h2', 'li', 'blockquote', 'pre', 'table']),
                'ordinal'   => 0,
                'index'     => fake()->numberBetween(0, 50),
                'text_hash' => str_pad(dechex(fake()->numberBetween(0, 0xFFFFFFFF)), 8, '0', STR_PAD_LEFT),
            ],
        ];
    }
}
