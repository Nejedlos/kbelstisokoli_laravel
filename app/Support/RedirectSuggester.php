<?php

namespace App\Support;

use App\Models\Page;
use App\Models\Post;
use App\Models\Team;
use App\Models\Gallery;
use Illuminate\Support\Str;

class RedirectSuggester
{
    /**
     * Navrhne cílovou URL pro zadanou (neexistující) cestu.
     */
    public static function suggest(string $url): string
    {
        $path = trim($url, '/');

        // Odstranění přípon
        $path = preg_replace('/\.(html|php|htm)$/i', '', $path);

        // Pokud je prázdné, vrátíme root
        if (empty($path)) {
            return '/';
        }

        // Získáme poslední část path (pro případ hlubokých URL)
        $segments = explode('/', $path);
        $slug = end($segments);

        // 1. Hledání v novinkách
        $post = Post::where('slug', $slug)
            ->orWhere('slug', 'like', '%' . $slug . '%')
            ->first();
        if ($post) {
            return '/novinky/' . $post->slug;
        }

        // 2. Hledání v týmech
        $team = Team::where('slug', $slug)
            ->orWhere('slug', 'like', '%' . $slug . '%')
            ->first();
        if ($team) {
            return '/tymy/' . $team->slug;
        }

        // 3. Hledání v galeriích
        $gallery = Gallery::where('slug', $slug)
            ->orWhere('slug', 'like', '%' . $slug . '%')
            ->first();
        if ($gallery) {
            return '/galerie/' . $gallery->slug;
        }

        // 4. Hledání v generických stránkách
        $page = Page::where('slug', $slug)
            ->orWhere('slug', 'like', '%' . $slug . '%')
            ->first();
        if ($page) {
            return '/' . $page->slug;
        }

        // 5. Zvláštní případy (časté chyby)
        if (Str::contains($path, 'kontakt')) return '/kontakt';
        if (Str::contains($path, 'trenink')) return '/treninky';
        if (Str::contains($path, 'zapas')) return '/zapasy';
        if (Str::contains($path, 'tym')) return '/tymy';
        if (Str::contains($path, 'galer')) return '/galerie';
        if (Str::contains($path, 'novin')) return '/novinky';

        return '/';
    }
}
