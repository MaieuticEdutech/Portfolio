<?php

namespace Database\Factories;

use App\Models\PortfolioClient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PortfolioClient>
 */
class PortfolioClientFactory extends Factory
{
    protected $model = PortfolioClient::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'category' => fake()->randomElement(PortfolioClient::CATEGORIES),
            'project_type' => fake()->randomElement(['Brand film', 'Program explainer', 'Training series']),
            'year' => (string) fake()->numberBetween(2022, 2025),
            'accent_gradient_start' => '#15D9A1',
            'accent_gradient_mid' => '#008680',
            'accent_gradient_end' => '#00615C',
            'tile_size' => fake()->randomElement(PortfolioClient::TILE_SIZES),
            'sort_order' => fake()->numberBetween(0, 50),
            'is_published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn () => ['is_published' => false]);
    }

    public function educational(): static
    {
        return $this->state(fn () => ['category' => 'educational']);
    }

    public function corporate(): static
    {
        return $this->state(fn () => ['category' => 'corporate']);
    }
}
