<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Post;
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
        return $this->add(__('general.nav.home'), route('public.home'));
    }

    public function generateForPage(Page $page): self
    {
        $this->addHome();

        // Zde by mohla být logika pro parent stránky, pokud by Page model měl parent_id
        return $this->add($page->title);
    }

    public function generateForPost(Post $post): self
    {
        $this->addHome();
        $this->add(__('general.nav.news'), route('public.news.index'));

        return $this->add($post->title);
    }
}
