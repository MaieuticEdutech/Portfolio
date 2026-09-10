<?php

use App\Models\PortfolioClient;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'filter', except: 'all')]
    public string $category = 'all';

    /** Show only the clients still missing assets. */
    #[Url(as: 'incomplete', except: false)]
    public bool $onlyIncomplete = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedOnlyIncomplete(): void
    {
        $this->resetPage();
    }

    public function togglePublished(int $clientId): void
    {
        $client = PortfolioClient::findOrFail($clientId);
        $client->update(['is_published' => ! $client->is_published]);

        $this->dispatch('toast', message: $client->is_published
            ? "{$client->name} is now live."
            : "{$client->name} is hidden from the portfolio.");
    }

    #[Computed]
    public function clients(): LengthAwarePaginator
    {
        return PortfolioClient::query()
            ->when(filled($this->search), fn ($q) => $q->whereLike('name', '%'.trim($this->search).'%', caseSensitive: false))
            ->category($this->category)
            ->when($this->onlyIncomplete, fn ($q) => $q->where(fn ($inner) => $inner
                ->whereNull('logo_path')
                ->orWhereDoesntHave('videos', fn ($v) => $v
                    ->whereNotNull('video_path')
                    ->orWhereNotNull('video_url'))))
            ->withCount([
                'videos',
                'videos as ready_videos_count' => fn ($q) => $q
                    ->whereNotNull('video_path')
                    ->orWhereNotNull('video_url'),
            ])
            ->inGridOrder()
            ->paginate(20);
    }
};
