<?php

namespace Tests\Feature\Public;

use App\Http\Controllers\Public\ClubEventController;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClubEventRoutesTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        parent::setUp();

        $app = new Application(dirname(__DIR__, 3));
        $this->router = new Router(new Dispatcher($app), $app);

        $app->instance('router', $this->router);
        Facade::setFacadeApplication($app);

        require dirname(__DIR__, 3).'/routes/public.php';
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);

        parent::tearDown();
    }

    public function test_event_detail_rejects_non_numeric_identifiers(): void
    {
        $route = collect($this->router->getRoutes())
            ->first(fn (Route $route) => $route->uri() === 'akce/{id}');

        if (! $route instanceof Route) {
            $this->fail('Trasa detailu akce nebyla zaregistrována.');
        }

        $this->assertFalse($route->matches(Request::create('/akce/null')));
        $this->assertTrue($route->matches(Request::create('/akce/83')));
    }

    public function test_event_controller_returns_not_found_for_non_numeric_identifier(): void
    {
        $this->expectException(NotFoundHttpException::class);

        (new ClubEventController)->show('null');
    }
}
