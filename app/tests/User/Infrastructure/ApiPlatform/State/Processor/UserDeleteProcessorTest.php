<?php

namespace App\Tests\User\Infrastructure\ApiPlatform\State\Processor;

use App\Tests\AbstractApiTestCase;
use App\Tests\Factory\UserFactory;

class UserDeleteProcessorTest extends AbstractApiTestCase
{
    public function testProcess(): void
    {
        // Arrange
        $this->loginAsUser(['ROLE_ADMIN']);
        $user = UserFactory::new()->create();

        // Act
        $this->request('DELETE', $this->url(['id' => $user->getId()]));

        // Assert
        self::assertResponseStatusCodeSame(204);
    }

    public function url(array $parameters = []): string
    {
        if (isset($parameters['id'])) {
            return sprintf('/api/users/%s', $parameters['id']);
        }

        return '/api/users';
    }
}
