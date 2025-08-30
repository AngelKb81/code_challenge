<?php

namespace Database\Factories;

use App\Models\Request;
use App\Models\User;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Request>
 */
class RequestFactory extends Factory
{
    protected $model = Request::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $requestTypes = ['existing_item', 'purchase_request'];
        $requestType = $this->faker->randomElement($requestTypes);
        $statuses = ['pending', 'approved', 'rejected', 'in_use', 'returned', 'overdue'];
        $priorities = ['low', 'medium', 'high', 'urgent'];

        $startDate = $this->faker->dateTimeBetween('now', '+30 days');
        $endDate = $this->faker->dateTimeBetween($startDate, '+60 days');

        $baseData = [
            'user_id' => User::factory(),
            'start_date' => Carbon::instance($startDate)->format('Y-m-d'),
            'end_date' => Carbon::instance($endDate)->format('Y-m-d'),
            'reason' => $this->faker->sentence(),
            'status' => $this->faker->randomElement($statuses),
            'priority' => $this->faker->randomElement($priorities),
            'quantity_requested' => $this->faker->numberBetween(1, 5),
            'request_type' => $requestType,
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];

        if ($requestType === 'existing_item') {
            $baseData['item_id'] = Item::factory();
        } else {
            // purchase_request
            $categories = ['Electronics', 'Furniture', 'Tools', 'Vehicles', 'Equipment'];
            $brands = ['Dell', 'HP', 'Apple', 'Samsung', 'Sony', 'Lenovo', 'IKEA', 'Steelcase'];

            $baseData['item_name'] = $this->faker->words(3, true);
            $baseData['item_category'] = $this->faker->randomElement($categories);
            $baseData['item_brand'] = $this->faker->optional(0.8)->randomElement($brands);
            $baseData['item_description'] = $this->faker->optional(0.7)->sentence();
        }

        return $baseData;
    }

    /**
     * Indicate that the request is pending.
     */
    public function pending()
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'pending',
            'admin_id' => null,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Indicate that the request is approved.
     */
    public function approved()
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the request is rejected.
     */
    public function rejected()
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'rejected',
            'rejection_reason' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the request is returned.
     */
    public function returned()
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'returned',
        ]);
    }

    /**
     * Create an existing item request.
     */
    public function existingItem()
    {
        return $this->state(fn(array $attributes) => [
            'request_type' => 'existing_item',
            'item_id' => Item::factory(),
            'item_name' => null,
            'item_category' => null,
            'item_brand' => null,
            'item_description' => null,
        ]);
    }

    /**
     * Create a new item request.
     */
    public function newItem()
    {
        $categories = ['Electronics', 'Furniture', 'Tools', 'Vehicles', 'Equipment'];
        $brands = ['Dell', 'HP', 'Apple', 'Samsung', 'Sony', 'Lenovo', 'IKEA', 'Steelcase'];

        return $this->state(fn(array $attributes) => [
            'request_type' => 'purchase_request',
            'item_id' => null,
            'item_name' => $this->faker->words(3, true),
            'item_category' => $this->faker->randomElement($categories),
            'item_brand' => $this->faker->optional(0.8)->randomElement($brands),
            'item_description' => $this->faker->optional(0.7)->sentence(),
        ]);
    }

    /**
     * Create a request with specific priority.
     */
    public function priority(string $priority)
    {
        return $this->state(fn(array $attributes) => [
            'priority' => $priority,
        ]);
    }

    /**
     * Create a request for specific dates.
     */
    public function dates(string $startDate, string $endDate)
    {
        return $this->state(fn(array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    /**
     * Create a request with specific quantity.
     */
    public function quantity(int $quantity)
    {
        return $this->state(fn(array $attributes) => [
            'quantity_requested' => $quantity,
        ]);
    }

    /**
     * Create a request for a specific user.
     */
    public function forUser(User $user)
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a request for a specific item.
     */
    public function forItem(Item $item)
    {
        return $this->state(fn(array $attributes) => [
            'request_type' => 'existing_item',
            'item_id' => $item->id,
            'item_name' => null,
            'item_category' => null,
            'item_brand' => null,
            'item_description' => null,
        ]);
    }
}
