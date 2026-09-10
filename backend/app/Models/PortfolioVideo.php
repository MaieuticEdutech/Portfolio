<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PortfolioVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'portfolio_client_id',
        'title',
        'video_url',
        'video_path',
        'thumbnail_path',
        'duration_label',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(PortfolioClient::class, 'portfolio_client_id');
    }

    /**
     * Uploaded file wins over a pasted external URL.
     */
    public function source(): ?string
    {
        if ($this->video_path) {
            return Storage::disk(config('filesystems.media'))->url($this->video_path);
        }

        return $this->video_url;
    }

    public function thumbnailUrl(): ?string
    {
        return $this->thumbnail_path
            ? Storage::disk(config('filesystems.media'))->url($this->thumbnail_path)
            : null;
    }

    public function isUploaded(): bool
    {
        return filled($this->video_path);
    }

    /**
     * Pasted YouTube/Vimeo links need an iframe, not a <video> tag.
     */
    public function embedUrl(): ?string
    {
        if ($this->isUploaded() || blank($this->video_url)) {
            return null;
        }

        $url = $this->video_url;

        if (preg_match('#(?:youtube\.com/(?:watch\?v=|embed/)|youtu\.be/)([\w-]{11})#i', $url, $m)) {
            return "https://www.youtube.com/embed/{$m[1]}";
        }

        if (preg_match('#vimeo\.com/(?:video/)?(\d+)#i', $url, $m)) {
            return "https://player.vimeo.com/video/{$m[1]}";
        }

        return null;
    }

    public function isPending(): bool
    {
        return blank($this->source());
    }
}
