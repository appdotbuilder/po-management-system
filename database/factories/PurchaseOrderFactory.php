<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\PurchaseOrder>
     */
    protected $model = PurchaseOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'po_number' => 'PO-' . now()->year . '-' . str_pad((string)fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraphs(2, true),
            'estimated_value' => $this->faker->randomFloat(2, 1000, 100000),
            'status' => $this->faker->randomElement(['draft', 'pending_validation', 'validated', 'in_progress', 'completed']),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'required_by' => $this->faker->dateTimeBetween('+1 week', '+3 months'),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the purchase order is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Indicate that the purchase order is pending validation.
     */
    public function pendingValidation(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending_validation',
        ]);
    }

    /**
     * Indicate that the purchase order is validated.
     */
    public function validated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'validated',
            'validated_by' => User::factory(),
            'validated_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'validation_notes' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the purchase order is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'validated_by' => User::factory(),
            'validated_at' => $this->faker->dateTimeBetween('-2 weeks', '-1 week'),
            'validation_notes' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the purchase order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'validated_by' => User::factory(),
            'validated_at' => $this->faker->dateTimeBetween('-1 month', '-2 weeks'),
            'validation_notes' => $this->faker->sentence(),
            'completed_by' => User::factory(),
            'completed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'completion_notes' => $this->faker->sentence(),
        ]);
    }
}