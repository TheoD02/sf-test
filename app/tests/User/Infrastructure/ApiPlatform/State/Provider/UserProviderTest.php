<?php

declare(strict_types=1);

namespace App\Tests\User\Infrastructure\ApiPlatform\State\Provider;

use App\Tests\AbstractApiTestCase;
use App\Tests\Factory\UserFactory;
use App\User\Domain\Security\UserPermissionEnum;
use App\User\Infrastructure\ApiPlatform\Resource\UserResource;

/**
 * @internal
 */
final class UserProviderTest extends AbstractApiTestCase
{
    public function testProvide(): void
    {
        // Arrange
        $this->loginAsUser([UserPermissionEnum::GET_ONE]);
        $user = UserFactory::new()->create();

        // Act
        $this->request('GET', $this->url([
            'id' => $user->getId() ?? 0,
        ]));

        // Assert
        self::assertResponseStatusCodeSame(200);
        self::assertMatchesResourceItemJsonSchema(UserResource::class);
        self::assertJsonContains([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);
    }

    /**
     * @param array{id?: int} $parameters
     */
    #[\Override]
    public function url(array $parameters = []): string
    {
        if (! isset($parameters['id'])) {
            throw new \InvalidArgumentException('Missing required parameter "id".');
        }

        return "/api/users/{$parameters['id']}";
    }
}
