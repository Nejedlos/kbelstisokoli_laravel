<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Post;
use App\Models\Gallery;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $pages = Page::where('status', 'published')
            ->where('is_visible', true)
            ->get();

        $posts = Post::where('status', 'published')
            ->where('is_visible', true)
            ->where(function($query) {
                $query->whereNull('publish_at')
                      ->orWhere('publish_at', '<=', now());
            })
            ->get();

        $galleries = Gallery::where('is_public', true)
            ->where('is_visible', true)
            ->get();

        $content = view('public.sitemap', [
            'pages' => $pages,
            'posts' => $posts,
            'galleries' => $galleries,
        ])->render();

        return response($content, 200)
            ->header('Content-Type', 'text/xml');
    }

    public function robots(): Response
    {
        $settings = app(\App\Services\BrandingService::class)->getSettings();
        $index = filter_var($settings['seo_robots_index'] ?? true, FILTER_VALIDATE_BOOLEAN);

        $robots = "User-agent: *\n";

        if ($index) {
            $robots .= "Allow: /\n";
            $robots .= "Disallow: /admin/\n";
            $robots .= "Disallow: /member/\n";
            $robots .= "Sitemap: " . url('/sitemap.xml') . "\n";
        } else {
            $robots .= "Disallow: /\n";
        }

        return response($robots, 200)
            ->header('Content-Type', 'text/plain');
    }
}
