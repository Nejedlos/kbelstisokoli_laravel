<?php

namespace App\Livewire\Member;

use App\Services\Help\HelpService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;

class HelpCenter extends Component
{
    #[Url(as: 'file')]
    public ?string $currentFile = null;

    #[Url(as: 'cat')]
    public ?string $currentCategory = null;

    #[Url(as: 'q')]
    public string $searchQuery = '';

    public function render()
    {
        return view('livewire.member.help-center', [
            'tree' => $this->getTree(),
            'homeData' => $this->getHomeData(),
            'categoryData' => $this->getCategoryData(),
            'articleData' => $this->getArticleData(),
            'searchResults' => $this->searchQuery ? $this->getSearchResults() : collect(),
            'userRoles' => auth()->user()->getRoleNames()->toArray(),
        ])->layout('layouts.member');
    }

    public function getTree(): Collection
    {
        return $this->getHelpService()->getNavigationTree();
    }

    public function getHomeData(): array
    {
        return $this->getHelpService()->getHomeData();
    }

    public function getSearchResults(): Collection
    {
        return $this->getHelpService()->search($this->searchQuery);
    }

    public function getCategoryData(): ?array
    {
        if (!$this->currentCategory) {
            return null;
        }
        return $this->getHelpService()->getCategoryData($this->currentCategory);
    }

    public function getArticleData(): ?array
    {
        if (!$this->currentFile) {
            return null;
        }
        return $this->getHelpService()->getArticleData($this->currentFile);
    }

    protected ?HelpService $helpService = null;

    protected function getHelpService(): HelpService
    {
        if ($this->helpService === null) {
            $this->helpService = app(HelpService::class)->forAudience(auth()->user()->getRoleNames()->toArray());
        }
        return $this->helpService;
    }

    public function resetSearch(): void
    {
        $this->searchQuery = '';
    }

    public function selectCategory(?string $slug): void
    {
        $this->currentCategory = $slug;
        $this->currentFile = null;
        $this->searchQuery = '';
    }

    public function selectArticle(?string $slug): void
    {
        $this->currentFile = $slug;
        $this->currentCategory = null;
        $this->searchQuery = '';
    }

    public function goHome(): void
    {
        $this->currentFile = null;
        $this->currentCategory = null;
        $this->searchQuery = '';
    }
}
