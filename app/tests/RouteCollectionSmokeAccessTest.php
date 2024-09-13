<?php

namespace App\Tests;

use App\Tests\Factory\UserFactory;
use Symfony\Component\Routing\CompiledRoute;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class RouteCollectionSmokeAccessTest extends AbstractApiTestCase
{
    private const array GLOBALLY_IGNORE = [];

    /**
     * @dataProvider provideRoutes
     */
    public function testRouteCollectionSmokeAccess(
        Route $route,
        CompiledRoute $compiledRoute,
        string $routeName,
    ): void
    {
        $this->loginAsUser(persist: true);
        $path = $route->getPath();
        $this->request('GET', $path);

        $response = self::getClient()->getResponse();
        if ($response->isSuccessful() === false) {
            $this->fail(
                sprintf(
                    '[HTTP %d][%s] Failed to call route %s (%s) %s%s',
                    $response->getStatusCode(),
                    $route->getDefault('_controller'),
                    $path,
                    $routeName,
                    PHP_EOL,
                    $response->getContent(),
                )
            );
        }
        self::assertResponseIsSuccessful();
    }

    public static function provideRoutes(): \Generator
    {
        /** @var RouteCollection $routes */
        $routes = static::getContainer()
            ->get(RouterInterface::class)
            ->getRouteCollection();

        self::ensureKernelShutdown();

        foreach ($routes as $routeName => $route) {
            if (in_array($routeName, self::GLOBALLY_IGNORE, true)) {
                continue;
            }
            $methods = $route->getMethods();
            if ($methods === []) {
                $methods = ['GET'];
            }
            $compiledRoute = $route->compile();
            if ($compiledRoute->getPathVariables() !== [] && $compiledRoute->getPathVariables() !== ['tenantCode']) {
                continue;
            }

            if (!in_array('GET', $methods, true)) {
                continue;
            }

            yield $routeName => [$route, $compiledRoute, $routeName];
        }
    }

    public function url(array $parameters = []): string
    {
        return 'dummy';
    }
}
