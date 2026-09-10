<?php

use App\Models\PortfolioClient;
use App\Models\PortfolioVideo;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    /**
     * Headline numbers for the studio: what is published and what is
     * still waiting on real assets.
     */
    #[Computed]
    public function stats(): array
    {
        return [
            'clients' => PortfolioClient::count(),
            'published' => PortfolioClient::published()->count(),
            'missingLogo' => PortfolioClient::whereNull('logo_path')->count(),
            'videos' => PortfolioVideo::count(),
            'pendingVideos' => PortfolioVideo::whereNull('video_path')->whereNull('video_url')->count(),
        ];
    }
};
