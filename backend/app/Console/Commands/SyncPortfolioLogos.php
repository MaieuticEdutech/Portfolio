<?php

namespace App\Console\Commands;

use App\Models\PortfolioClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SyncPortfolioLogos extends Command
{
    protected $signature = 'portfolio:sync-logos
                            {--dir=logos : Folder on the logo disk that holds the logo files}
                            {--force : Clear every logo path even when no files were found}';

    protected $description = 'Link logo files on the logo disk to clients by slug (logos/<slug>.webp)';

    private const EXTENSIONS = ['webp', 'png', 'svg', 'jpg', 'jpeg'];

    public function handle(): int
    {
        $disk = Storage::disk(config('filesystems.logos'));
        $dir = trim($this->option('dir'), '/');

        $filesBySlug = collect($disk->files($dir))
            ->filter(fn (string $path) => in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::EXTENSIONS, true))
            ->keyBy(fn (string $path) => pathinfo($path, PATHINFO_FILENAME));

        // On local disk an empty folder genuinely means the logos were removed.
        // On a remote disk it far more often means the bucket or credentials are
        // wrong, and clearing every path would throw away real work.
        $diskName = config('filesystems.logos');
        $isRemote = config("filesystems.disks.{$diskName}.driver") !== 'local';

        if ($isRemote && $filesBySlug->isEmpty() && ! $this->option('force')) {
            $this->components->error(
                "No logo files found in [{$dir}] on the remote [{$diskName}] disk. "
                .'Refusing to clear existing logo paths. Check LOGO_DISK and the bucket '
                .'contents, or pass --force if this is intentional.'
            );

            return self::FAILURE;
        }

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
