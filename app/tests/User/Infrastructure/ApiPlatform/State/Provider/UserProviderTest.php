<?php

namespace App\Tests\User\Infrastructure\ApiPlatform\State\Provider;

use App\Tests\AbstractApiTestCase;
use App\Tests\Factory\UserFactory;
use App\User\Infrastructure\ApiPlatform\Resource\UserResource;

class UserProviderTest extends AbstractApiTestCase
{

    public function testProvide()
    {
        // Arrange
        $this->loginAsUser();
        $user = UserFactory::new()->create();

        // Act
        $this->request('GET', $this->url(['id' => $user->getId()]));

        // Assert
        self::assertResponseStatusCodeSame(200);
        self::assertMatchesResourceItemJsonSchema(UserResource::class);
        self::assertJsonContains([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ]);
    }

    public function url(array $parameters = []): string
    {
        if (! isset($parameters['id'])) {
            throw new \InvalidArgumentException('Missing required parameter "id".');
        }

        return "/api/users/{$parameters['id']}";
    }
}
