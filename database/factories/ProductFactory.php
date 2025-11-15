<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'          => ucfirst($this->faker->words(3, true)),
            'sku'           => strtoupper($this->faker->unique()->bothify('PRD-#####')),
            'description'   => $this->faker->sentence(),
            'purchase_price'=> $this->faker->numberBetween(10000, 500000),
            'sale_price'    => $this->faker->numberBetween(15000, 750000),
            'min_stock'     => $this->faker->numberBetween(5, 50),
            'current_stock' => $this->faker->numberBetween(0, 200),
            'unit'          => $this->faker->randomElement(['pcs', 'box', 'kg']),
            'rack_location' => 'R-' . $this->faker->bothify('##-??'),
            'image_path'    => null,
        ];
    }
}
