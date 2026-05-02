<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoTest extends TestCase
{
    /** @test */
    public function robots_txt_is_accessible_and_contains_sitemap()
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertSee('Sitemap: https://kbelstisokoli.cz/sitemap.xml');
        $response->assertSee('Disallow: /admin');
    }

    /** @test */
    public function llms_txt_is_accessible()
    {
        $response = $this->get('/llms.txt');

        $response->assertStatus(200);
        $response->assertSee('llms.txt - Kbelští sokoli');
    }

    /** @test */
    public function sitemap_xml_is_accessible()
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=utf-8');
        $response->assertSee('<urlset', false);
        $response->assertSee('https://kbelstisokoli.cz', false);
    }
}
