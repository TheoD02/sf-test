<?php

declare(strict_types=1);

namespace App\Tests\Shared\Controller;

use App\Tests\AbstractApiTestCase;

/**
 * @internal
 */
final class PingControllerTest extends AbstractApiTestCase
{
    public function testPing(): void
    {
        // Act
        $this->request('GET', $this->url());

        // Assert
        self::assertResponseContent([
            'status' => 'ok',
            'deploy_time' => false,
        ]);
    }

    #[\Override]
    public function url(array $parameters = []): string
    {
        return '/api/ping';
    }
}
