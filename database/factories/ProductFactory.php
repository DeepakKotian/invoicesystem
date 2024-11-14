<?php

namespace Database\Factories;

use App\Models\Category;
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
    public function definition()
    {
        $category = Category::factory()->create();
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'price' => 100.0,
            'quantity' => 10,
            'category_id' => $category->id,
        ];
    }
}
