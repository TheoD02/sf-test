<?php

namespace App\Tests\User;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Factory\UserFactory;
use App\Tests\AbstractApiTestCase;
use App\User\Infrastructure\ApiPlatform\Resource\UserResource;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserResourceTest extends AbstractApiTestCase
{
    use ResetDatabase;

    public function testGetUser(): void
    {
        $this->request('GET', '/api/users/1');

        self::assertResponseIsSuccessful();
        self::assertMatchesResourceItemJsonSchema(UserResource::class);
    }
}
