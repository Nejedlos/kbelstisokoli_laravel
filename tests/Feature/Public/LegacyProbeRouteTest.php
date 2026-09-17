<?php

namespace Tests\Feature\Public;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LegacyProbeRouteTest extends TestCase
{
    public function test_nested_php_probe_returns_not_found_without_querying_cms_pages(): void
    {
        $queriedCmsPages = false;

        DB::listen(function (QueryExecuted $query) use (&$queriedCmsPages): void {
            if (str_contains($query->sql, 'new_pages')) {
                $queriedCmsPages = true;
            }
        });

        $this->get('/jedeme/images/help/help/rss-novinky.php')
            ->assertNotFound();

        $this->assertFalse($queriedCmsPages);
    }
}
