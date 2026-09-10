<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortfolioClient extends Model
{
    use HasFactory;

    public const CATEGORIES = ['educational', 'corporate'];

    public const TILE_SIZES = ['big', 'med', 'small'];

    protected $fillable = [
        'name',
        'slug',
        'category',
        'project_type',
        'year',
        'accent_gradient_start',
        'accent_gradient_mid',
        'accent_gradient_end',
        'logo_path',
        'tile_size',
        'sort_order',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $client) {
            if (blank($client->slug)) {
                $client->slug = static::uniqueSlug($client->name, $client->getKey());
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'client';
        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    public function videos(): HasMany
    {
        return $this->hasMany(PortfolioVideo::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * The film a tile plays on hover: the first with footage actually uploaded.
     * Pasted YouTube/Vimeo links are excluded because a <video> tag cannot
     * play them, and a client with no upload simply gets no preview.
     */
    public function previewVideo(): HasOne
    {
        return $this->hasOne(PortfolioVideo::class)->ofMany(
            ['sort_order' => 'min', 'id' => 'min'],
            fn (Builder $query) => $query->whereNotNull('video_path')
        );
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        return $query->when(
            $category && $category !== 'all',
            fn (Builder $q) => $q->where('category', $category)
        );
    }

    /**
     * Grid order: explicit sort_order first, then bigger tiles, then name.
     */
    public function scopeInGridOrder(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderByRaw("CASE tile_size WHEN 'big' THEN 0 WHEN 'med' THEN 1 ELSE 2 END")
            ->orderBy('name');
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path
            ? Storage::disk(config('filesystems.media'))->url($this->logo_path)
            : null;
    }

    /**
     * Falls back to the brand gradient wash while real logos are pending.
     */
    public function gradientStops(): array
    {
        return array_values(array_filter([
            $this->accent_gradient_start,
            $this->accent_gradient_mid,
            $this->accent_gradient_end,
        ]));
    }
}
