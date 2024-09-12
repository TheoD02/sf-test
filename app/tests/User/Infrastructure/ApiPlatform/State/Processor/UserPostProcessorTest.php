<?php

namespace App\Tests\User\Infrastructure\ApiPlatform\State\Processor;

use App\Tests\AbstractApiTestCase;
use App\User\Infrastructure\ApiPlatform\Resource\UserResource;

class UserPostProcessorTest extends AbstractApiTestCase
{
    public function testProcess(): void
    {
        // Arrange
        $this->loginAsUser(['ROLE_ADMIN']);

        // Act
        $this->request('POST', $this->url(), [
            'json' => [
                'email' => 'new@phpunit.com',
                'password' => 'test',
                'roles' => ['ROLE_USER'],
            ],
        ]);

        // Assert
        self::assertResponseStatusCodeSame(201);
        self::assertMatchesResourceItemJsonSchema(UserResource::class);
        self::assertJsonContains([
            'email' => 'new@phpunit.com',
            'roles' => ['ROLE_USER'],
        ]);
    }

    public function url(array $parameters = []): string
    {
        return '/api/users';
    }
}
