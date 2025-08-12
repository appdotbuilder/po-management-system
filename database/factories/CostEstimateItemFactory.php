<?php

namespace Database\Factories;

use App\Models\CostEstimate;
use App\Models\CostEstimateItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CostEstimateItem>
 */
class CostEstimateItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\CostEstimateItem>
     */
    protected $model = CostEstimateItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(2, 1, 100);
        $unitPrice = $this->faker->randomFloat(2, 10, 1000);

        return [
            'cost_estimate_id' => CostEstimate::factory(),
            'item_code' => $this->faker->optional()->regexify('[A-Z]{2}[0-9]{4}'),
            'description' => $this->faker->sentence(3),
            'unit' => $this->faker->randomElement(['pcs', 'kg', 'm', 'm²', 'm³', 'hr', 'day', 'lot']),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice,
            'notes' => $this->faker->optional()->sentence(),
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }
}