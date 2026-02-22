<?php

namespace App\Services;

use Illuminate\Support\Collection;

class BreadcrumbService
{
    protected Collection $breadcrumbs;

    public function __construct()
    {
        $this->breadcrumbs = collect();
    }

    public function add(string $title, ?string $url = null): self
    {
        $this->breadcrumbs->push((object) [
            'title' => $title,
            'url' => $url,
        ]);

        return $this;
    }

    public function get(): Collection
    {
        return $this->breadcrumbs;
    }

    public function addHome(): self
    {
        return $this->add(__('nav.home'), route('public.home'));
    }

    public function generateForPage(\App\Models\Page $page): self
    {
        $this->addHome();
        // Zde by mohla být logika pro parent stránky, pokud by Page model měl parent_id
        return $this->add($page->title);
    }

    public function generateForPost(\App\Models\Post $post): self
    {
        $this->addHome();
        $this->add(__('nav.news'), route('public.news.index'));
        return $this->add($post->title);
    }
}
