<?php

namespace App\Console\Commands;

use App\Models\PortfolioClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncPortfolioLogos extends Command
{
    protected $signature = 'portfolio:sync-logos
                            {--dir=logos : Folder on the public disk that holds the logo files}';

    protected $description = 'Link logo files on the public disk to clients by slug (logos/<slug>.webp)';

    private const EXTENSIONS = ['webp', 'png', 'svg', 'jpg', 'jpeg'];

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $dir = trim($this->option('dir'), '/');

        $filesBySlug = collect($disk->files($dir))
            ->filter(fn (string $path) => in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::EXTENSIONS, true))
            ->keyBy(fn (string $path) => pathinfo($path, PATHINFO_FILENAME));

        $linked = 0;
        $cleared = 0;
        $missing = [];

        foreach (PortfolioClient::orderBy('name')->get() as $client) {
            $path = $filesBySlug->pull($client->slug);

            if ($path === null) {
                $missing[] = $client->slug;

                if ($client->logo_path !== null) {
                    $client->update(['logo_path' => null]);
                    $cleared++;
                }

                continue;
            }

            if ($client->logo_path !== $path) {
                $client->update(['logo_path' => $path]);
                $linked++;
            }
        }

        $this->components->info("Linked {$linked} logo(s), cleared {$cleared} stale path(s).");

        if ($filesBySlug->isNotEmpty()) {
            $this->components->warn('Files with no matching client slug: '.$filesBySlug->keys()->implode(', '));
        }

        if ($missing !== []) {
            $this->components->warn('Clients still without a logo: '.implode(', ', $missing));
        }

        return self::SUCCESS;
    }
}
