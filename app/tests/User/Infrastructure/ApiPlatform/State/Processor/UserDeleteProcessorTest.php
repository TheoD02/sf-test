<?php

declare(strict_types=1);

namespace App\Tests\User\Infrastructure\ApiPlatform\State\Processor;

use App\Tests\AbstractApiTestCase;
use App\Tests\Factory\UserFactory;

/**
 * @internal
 */
final class UserDeleteProcessorTest extends AbstractApiTestCase
{
    public function testProcess(): void
    {
        // Arrange
        $this->loginAsUser(['ROLE_ADMIN']);
        $user = UserFactory::new()->create();

        // Act
        $this->request('DELETE', $this->url([
            'id' => $user->getId() ?? 0,
        ]));

        // Assert
        self::assertResponseStatusCodeSame(204);
    }

    /**
     * @param array{id?: int} $parameters
     */
    #[\Override]
    public function url(array $parameters = []): string
    {
        if (isset($parameters['id'])) {
            return \sprintf('/api/users/%s', $parameters['id']);
        }

        return '/api/users';
    }
}
