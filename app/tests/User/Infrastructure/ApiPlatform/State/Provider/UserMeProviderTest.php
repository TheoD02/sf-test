<?php

namespace App\Tests\User\Infrastructure\ApiPlatform\State\Provider;

use App\Tests\AbstractApiTestCase;
use App\User\Infrastructure\ApiPlatform\Resource\UserResource;

class UserMeProviderTest extends AbstractApiTestCase
{

    public function testProvide(): void
    {
        // Arrange
        $this->loginAsUser(persist: true);

        // Act
        $this->request('GET', $this->url());

        // Assert
        self::assertResponseStatusCodeSame(200);
        self::assertMatchesResourceItemJsonSchema(UserResource::class);
    }

    public function url(array $parameters = []): string
    {
        return '/api/me';
    }
}
