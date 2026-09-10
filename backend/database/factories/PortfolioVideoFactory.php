<?php

namespace Database\Factories;

use App\Models\PortfolioClient;
use App\Models\PortfolioVideo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PortfolioVideo>
 */
class PortfolioVideoFactory extends Factory
{
    protected $model = PortfolioVideo::class;

    public function definition(): array
    {
        return [
            'portfolio_client_id' => PortfolioClient::factory(),
            'title' => fake()->sentence(3),
            'sort_order' => 0,
        ];
    }

    public function uploaded(): static
    {
        return $this->state(fn () => ['video_path' => 'portfolio/videos/sample.mp4']);
    }

    public function youtube(): static
    {
        return $this->state(fn () => ['video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);
    }
}
