<?php

namespace App\Tests\Shared\Controller;

use App\Tests\AbstractApiTestCase;

class PingControllerTest extends AbstractApiTestCase
{
    public function testPing(): void
    {
        // Act
        $this->request('GET', $this->url());

        // Assert
        self::assertResponseContent(['status' => 'ok']);
    }

    public function url(array $parameters = []): string
    {
        return '/api/ping';
    }
}
