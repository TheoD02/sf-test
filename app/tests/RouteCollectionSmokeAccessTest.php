<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Component\Routing\CompiledRoute;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 */
final class RouteCollectionSmokeAccessTest extends AbstractApiTestCase
{
    private const array GLOBALLY_IGNORE = ['gesdinet_jwt_refresh_token', 'app_logout'];

    public static function provideRouteCollectionSmokeAccessCases(): iterable
    {
        /** @var RouteCollection $routes */
        $routes = self::getContainer()
            ->get(RouterInterface::class)
            ->getRouteCollection()
        ;

        self::ensureKernelShutdown();

        foreach ($routes as $routeName => $route) {
            if (\in_array($routeName, self::GLOBALLY_IGNORE, true)) {
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

            if (! \in_array('GET', $methods, true)) {
                continue;
            }

            yield $routeName => [$route, $compiledRoute, $routeName];
        }
    }

    /**
     * @dataProvider provideRouteCollectionSmokeAccessCases
     */
    public function testRouteCollectionSmokeAccess(
        Route $route,
        CompiledRoute $compiledRoute,
        string $routeName,
    ): void {
        $this->loginAsUser(persist: true);
        $path = $route->getPath();
        $this->request('GET', $path);

        $response = self::getClient()->getResponse();
        if ($response->isSuccessful() === false) {
            $this->fail(
                \sprintf(
                    '[HTTP %d][%s] Failed to call route %s (%s) %s%s',
                    $response->getStatusCode(),
                    $route->getDefault('_controller'),
                    $path,
                    $routeName,
                    \PHP_EOL,
                    $response->getContent(),
                ),
            );
        }

        self::assertResponseIsSuccessful();
    }

    #[\Override]
    public function url(array $parameters = []): string
    {
        return 'dummy';
    }
}
