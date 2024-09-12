<?php

namespace Module\ApiPlatformEasyFilter\Tests\Shared\Controller;

use App\Shared\Controller\PingController;
use App\Tests\AbstractApiTestCase;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PingControllerTest extends AbstractApiTestCase
{
    public function testPing(): void
    {
        // Act
        $this->request('GET', '/api/ping');

        // Assert
        self::assertResponseContent(['status' => 'ok']);
    }
}
