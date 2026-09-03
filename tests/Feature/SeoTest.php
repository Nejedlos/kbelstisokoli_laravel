<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class SeoTest extends TestCase
{
    #[Test]
    public function robots_txt_is_accessible_and_contains_sitemap()
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertSee('Sitemap: https://kbelstisokoli.cz/sitemap.xml');
        $response->assertSee('Disallow: /admin');
    }

    #[Test]
    public function llms_txt_is_accessible()
    {
        $response = $this->get('/llms.txt');

        $response->assertStatus(200);
        $response->assertSee('llms.txt - Kbelští sokoli');
    }

    #[Test]
    public function sitemap_xml_is_accessible()
    {
        // Exercise generation independently of an ignored local sitemap export.
        $originalPublicPath = public_path();
        $this->app->usePublicPath(sys_get_temp_dir().'/ks-sitemap-'.Str::uuid());
        try {
            $response = $this->get('https://kbelstisokoli.cz/sitemap.xml');
        } finally {
            $this->app->usePublicPath($originalPublicPath);
        }

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=utf-8');
        $xml = $response->baseResponse instanceof BinaryFileResponse
            ? file_get_contents($response->baseResponse->getFile()->getPathname())
            : $response->getContent();
        $this->assertNotFalse(simplexml_load_string($xml));
        $this->assertStringContainsString('<urlset', $xml);
        $this->assertStringContainsString('<loc>'.e(url('/')).'</loc>', $xml);
    }
}
