<?php

namespace Database\Factories;

use App\Models\CostEstimate;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CostEstimate>
 */
class CostEstimateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\CostEstimate>
     */
    protected $model = CostEstimate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'purchase_order_id' => PurchaseOrder::factory(),
            'ce_number' => 'CE-' . now()->year . '-' . str_pad((string)fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraphs(2, true),
            'type' => $this->faker->randomElement(['cost_estimate', 'bill_of_quantities']),
            'total_amount' => $this->faker->randomFloat(2, 5000, 150000),
            'status' => $this->faker->randomElement(['draft', 'pending_approval', 'approved', 'rejected']),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the cost estimate is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Indicate that the cost estimate is pending approval.
     */
    public function pendingApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending_approval',
        ]);
    }

    /**
     * Indicate that the cost estimate is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'approval_notes' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the cost estimate is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'rejection_notes' => $this->faker->sentence(),
        ]);
    }
}