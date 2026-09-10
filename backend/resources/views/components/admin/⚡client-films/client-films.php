<?php

use App\Models\PortfolioClient;
use App\Models\PortfolioVideo;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public PortfolioClient $client;

    /** The film currently open in the edit panel. */
    public ?int $editingId = null;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|url|max:2048')]
    public string $videoUrl = '';

    #[Validate('nullable|string|max:20')]
    public string $durationLabel = '';

    #[Validate('nullable|file|mimetypes:video/mp4,video/quicktime,video/webm|max:2097152')]
    public $videoFile = null;

    #[Validate('nullable|image|max:8192')]
    public $thumbnailFile = null;

    public function mount(PortfolioClient $client): void
    {
        $this->client = $client;
    }

    public function edit(int $videoId): void
    {
        $video = $this->client->videos()->findOrFail($videoId);

        $this->editingId = $video->id;
        $this->title = $video->title;
        $this->videoUrl = $video->video_url ?? '';
        $this->durationLabel = $video->duration_label ?? '';
        $this->reset('videoFile', 'thumbnailFile');
        $this->resetValidation();
    }

    public function newFilm(): void
    {
        $this->editingId = 0;
        $this->reset('title', 'videoUrl', 'durationLabel', 'videoFile', 'thumbnailFile');
        $this->resetValidation();
    }

    public function cancel(): void
    {
        $this->editingId = null;
        $this->reset('title', 'videoUrl', 'durationLabel', 'videoFile', 'thumbnailFile');
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate();

        $video = $this->editingId
            ? $this->client->videos()->findOrFail($this->editingId)
            : new PortfolioVideo(['portfolio_client_id' => $this->client->id]);

        $attributes = [
            'title' => $this->title,
            'video_url' => $this->videoUrl ?: null,
            'duration_label' => $this->durationLabel ?: null,
        ];

        if ($this->videoFile) {
            $this->deleteFile($video->video_path);
            $attributes['video_path'] = $this->videoFile->store($this->directory('films'), $this->disk());
        }

        if ($this->thumbnailFile) {
            $this->deleteFile($video->thumbnail_path);
            $attributes['thumbnail_path'] = $this->thumbnailFile->store($this->directory('thumbnails'), $this->disk());
        }

        if ($video->exists) {
            $video->update($attributes);
        } else {
            $attributes['sort_order'] = ($this->client->videos()->max('sort_order') ?? -1) + 1;
            $video->fill($attributes)->save();
        }

        $this->cancel();
        $this->client->refresh();

        $this->dispatch('toast', message: 'Film saved.');
    }

    /**
     * Clearing the file leaves the row in place so the slot can be refilled.
     */
    public function removeFootage(int $videoId): void
    {
        $video = $this->client->videos()->findOrFail($videoId);

        $this->deleteFile($video->video_path);
        $this->deleteFile($video->thumbnail_path);

        $video->update(['video_path' => null, 'thumbnail_path' => null, 'video_url' => null]);

        $this->client->refresh();
        $this->dispatch('toast', message: 'Footage removed.');
    }

    public function deleteFilm(int $videoId): void
    {
        $video = $this->client->videos()->findOrFail($videoId);

        $this->deleteFile($video->video_path);
        $this->deleteFile($video->thumbnail_path);
        $video->delete();

        if ($this->editingId === $videoId) {
            $this->cancel();
        }

        $this->client->refresh();
        $this->dispatch('toast', message: 'Film deleted.');
    }

    public function moveUp(int $videoId): void
    {
        $this->swapWithNeighbour($videoId, -1);
    }

    public function moveDown(int $videoId): void
    {
        $this->swapWithNeighbour($videoId, 1);
    }

    private function swapWithNeighbour(int $videoId, int $direction): void
    {
        $ordered = $this->client->videos()->get()->values();
        $index = $ordered->search(fn (PortfolioVideo $v) => $v->id === $videoId);

        if ($index === false || ! $ordered->has($index + $direction)) {
            return;
        }

        $current = $ordered[$index];
        $neighbour = $ordered[$index + $direction];

        // Sort values can collide after seeding, so rewrite both from the index.
        $current->update(['sort_order' => $index + $direction]);
        $neighbour->update(['sort_order' => $index]);

        $this->client->refresh();
    }

    private function disk(): string
    {
        return config('filesystems.media');
    }

    private function directory(string $kind): string
    {
        return "portfolio/{$kind}/{$this->client->slug}";
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk($this->disk())->delete($path);
        }
    }
};
