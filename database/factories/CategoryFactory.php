<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->unique()->word()).' Category',
            'description' => $this->faker->sentence(),
            'image_path' => null,
            'sku_prefix' => strtoupper($this->faker->unique()->lexify('???')),
        ];
    }
}
