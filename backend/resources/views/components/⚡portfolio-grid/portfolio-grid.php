<?php

use App\Models\PortfolioClient;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Url(as: 'filter', except: 'all')]
    public string $category = 'all';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public ?int $selectedClientId = null;

    public function updatedCategory(): void
    {
        $this->selectedClientId = null;
    }

    public function updatedSearch(): void
    {
        $this->selectedClientId = null;
    }

    public function filterBy(string $category): void
    {
        $this->category = in_array($category, PortfolioClient::CATEGORIES, true) ? $category : 'all';
        $this->selectedClientId = null;
    }

    public function select(int $clientId): void
    {
        $this->selectedClientId = $clientId;
    }

    public function close(): void
    {
        $this->selectedClientId = null;
    }

    #[Computed]
    public function clients(): Collection
    {
        return PortfolioClient::query()
            ->published()
            ->category($this->category)
            // whereLike keeps this case-insensitive on every driver; a plain
            // LIKE is case-sensitive on Postgres and would match nothing.
            ->when(filled($this->search), fn ($q) => $q->whereLike('name', '%'.trim($this->search).'%', caseSensitive: false))
            ->withCount('videos')
            ->inGridOrder()
            ->get();
    }

    /**
     * Drives the counts on the filter pills — unaffected by the active filter.
     */
    #[Computed]
    public function counts(): array
    {
        $byCategory = PortfolioClient::query()
            ->published()
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return [
            'all' => (int) $byCategory->sum(),
            'educational' => (int) $byCategory->get('educational', 0),
            'corporate' => (int) $byCategory->get('corporate', 0),
        ];
    }

    #[Computed]
    public function selectedClient(): ?PortfolioClient
    {
        if (! $this->selectedClientId) {
            return null;
        }

        return PortfolioClient::query()
            ->published()
            ->with('videos')
            ->find($this->selectedClientId);
    }
};
