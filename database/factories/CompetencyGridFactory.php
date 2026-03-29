<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\{GridType, GradingScheme, LifecycleStatus};

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompetencyGrid>
 */
class CompetencyGridFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'pedagogical_objective' => fake()->paragraph(),
            'grid_type' => GridType::Evaluation,
            'grading_scheme' => GradingScheme::Status,
            'instructions' => fake()->optional()->paragraph(),
            'lifecycle_status' => LifecycleStatus::InProgress,
            'created_by_id' => 1, // adapt
        ];
    }
}
