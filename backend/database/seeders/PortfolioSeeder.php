<?php

namespace Database\Seeders;

use App\Models\PortfolioClient;
use Illuminate\Database\Seeder;

class PortfolioSeeder extends Seeder
{
    /**
     * Gradient sets drawn only from the Maieutic brand palette
     * (teal family for educational, red-sea family for corporate).
     */
    private const GRADIENTS = [
        'educational' => [
            ['#15D9A1', '#008680', '#00615C'],
            ['#C1FAFB', '#008680', '#00615C'],
            ['#69FFF7', null, '#00615C'],
            ['#008680', '#00615C', '#003D3A'],
        ],
        'corporate' => [
            ['#F2AA84', '#A31009', '#800D07'],
            ['#F8847E', null, '#800D07'],
            ['#FEF1DE', '#F2AA84', '#A31009'],
            ['#A31009', '#800D07', '#4F0703'],
        ],
    ];

    private const PROJECT_TYPES = [
        'educational' => [
            'Program explainer',
            'Course promo series',
            'Admissions campaign',
            'Brand anthem film',
            'Faculty feature series',
        ],
        'corporate' => [
            'Corporate brand film',
            'Internal training series',
            'Leadership feature',
            'Product explainer',
            'Culture & careers film',
        ],
    ];

    private const YEARS = ['2022', '2023', '2023', '2024', '2024', '2025'];

    private const EDUCATIONAL = [
        'REVA University Online',
        'P P Savani University Online',
        'BML Munjal University',
        'Jaipur National University',
        'BIMTECH Birla Institute',
        'DSU Online',
        'Alliance University',
        'Symbiosis',
        'Emeritus',
        'Manipal / Unext',
        'Amity / Univo Edutech',
        'AISECT',
        'SimpliLearn',
        'upGrad',
        'Wiley',
        'Pearson India Pvt Ltd',
        'IEEE India Pvt Ltd',
        'Mysore University',
        'Avinashilingam University',
        'Wadhwani India Pvt. Ltd',
        'National Entrepreneurship Network',
        'NIMI',
        'Aster Health Academy',
        'Empower School of Health',
        'Analytics Vidhya',
    ];

    private const CORPORATE = [
        'HDFC',
        'Jade Global',
        'Strides Ltd',
        'HealthsMind',
        'TE Connectivity',
        'Saint Gobain',
        'Narayana Health',
        'Intas Pharma',
        'Mazars',
        'Wurth Elektronik',
        'Quodec Pvt Ltd',
        'First Source',
        'Far Eye',
        'Numocity',
        'ArcoLabs',
        'Terumo India Pvt Ltd',
        'Terumo Singapore',
        'Glen Mark Pharma',
        'Next Wealth',
        'Kreate Global',
    ];

    private const BIG_TILES = [
        'REVA University Online', 'Symbiosis', 'Emeritus', 'upGrad',
        'HDFC', 'Saint Gobain', 'Narayana Health', 'Glen Mark Pharma',
    ];

    private const MED_TILES = [
        'BML Munjal University', 'Alliance University', 'SimpliLearn', 'Manipal / Unext',
        'Pearson India Pvt Ltd', 'Wiley', 'DSU Online', 'Amity / Univo Edutech',
        'TE Connectivity', 'Intas Pharma', 'Terumo India Pvt Ltd', 'Wurth Elektronik',
        'First Source', 'Jade Global', 'Next Wealth', 'Strides Ltd',
    ];

    public function run(): void
    {
        $order = 0;

        foreach (['educational' => self::EDUCATIONAL, 'corporate' => self::CORPORATE] as $category => $names) {
            foreach ($names as $index => $name) {
                $gradient = self::GRADIENTS[$category][$index % count(self::GRADIENTS[$category])];
                $types = self::PROJECT_TYPES[$category];

                $client = PortfolioClient::updateOrCreate(
                    ['slug' => PortfolioClient::uniqueSlug($name)],
                    [
                        'name' => $name,
                        'category' => $category,
                        'project_type' => $types[$index % count($types)],
                        'year' => self::YEARS[$index % count(self::YEARS)],
                        'accent_gradient_start' => $gradient[0],
                        'accent_gradient_mid' => $gradient[1],
                        'accent_gradient_end' => $gradient[2],
                        'tile_size' => match (true) {
                            in_array($name, self::BIG_TILES, true) => 'big',
                            in_array($name, self::MED_TILES, true) => 'med',
                            default => 'small',
                        },
                        'sort_order' => $order++,
                        'is_published' => true,
                    ]
                );

                // three placeholder sample slots per client — real footage drops into these rows
                if ($client->videos()->count() === 0) {
                    foreach (range(1, 3) as $n) {
                        $client->videos()->create([
                            'title' => "{$name} — Sample {$n}",
                            'sort_order' => $n - 1,
                        ]);
                    }
                }
            }
        }
    }
}
