<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['Electronics', 'Furniture', 'Tools', 'Vehicles', 'Equipment'];
        $brands = ['Dell', 'HP', 'Apple', 'Samsung', 'Sony', 'Lenovo', 'IKEA', 'Steelcase'];
        
        return [
            'name' => $this->faker->words(3, true),
            'category' => $this->faker->randomElement($categories),
            'brand' => $this->faker->randomElement($brands),
            'quantity' => $this->faker->numberBetween(1, 10),
            'status' => 'available', // Default to available
            'description' => $this->faker->sentence(),
            'location' => 'Warehouse ' . $this->faker->randomLetter(),
            'serial_number' => $this->faker->unique()->regexify('[A-Z]{3}-[0-9]{6}'),
            'purchase_date' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'warranty_expiry' => $this->faker->optional(0.7)->dateTimeBetween('now', '+3 years'),
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    /**
     * Indicate that the item is available.
     */
    public function available()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'available',
            'quantity' => $this->faker->numberBetween(1, 10),
        ]);
    }

    /**
     * Indicate that the item is unavailable.
     */
    public function unavailable()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'not_available',
        ]);
    }

    /**
     * Indicate that the item is in maintenance.
     */
    public function maintenance()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'not_available',
        ]);
    }

    /**
     * Create an item with specific category.
     */
    public function category(string $category)
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category,
        ]);
    }

    /**
     * Create an item with low stock.
     */
    public function lowStock()
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $this->faker->numberBetween(1, 2),
            'status' => 'available',
        ]);
    }

    /**
     * Create an item with zero quantity.
     */
    public function outOfStock()
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 0,
            'status' => 'available',
        ]);
    }
}
