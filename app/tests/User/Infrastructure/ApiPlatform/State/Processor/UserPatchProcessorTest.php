<?php

declare(strict_types=1);

namespace App\Tests\User\Infrastructure\ApiPlatform\State\Processor;

use App\Tests\AbstractApiTestCase;
use App\Tests\Factory\UserFactory;
use App\User\Infrastructure\ApiPlatform\Resource\UserResource;

/**
 * @internal
 */
final class UserPatchProcessorTest extends AbstractApiTestCase
{
    public function testProcess(): void
    {
        // Arrange
        $this->loginAsUser(['ROLE_ADMIN']);
        $user = UserFactory::new()->create([
            'email' => 'old@phpunit.com',
            'roles' => ['ROLE_USER'],
        ]);

        // Act
        $this->request('PATCH', $this->url([
            'id' => $user->getId() ?? 0,
        ]), [
            'json' => [
                'email' => 'new@phpunit.com',
            ],
        ]);

        // Assert
        self::assertResponseStatusCodeSame(200);
        self::assertMatchesResourceItemJsonSchema(UserResource::class);
        self::assertJsonContains([
            'id' => $user->getId(),
            'email' => 'new@phpunit.com',
            'roles' => ['ROLE_USER'],
        ]);
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
