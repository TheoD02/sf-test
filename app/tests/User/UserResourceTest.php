<?php

namespace App\Tests\User;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Factory\UserFactory;
use App\User\Infrastructure\ApiPlatform\Resource\UserResource;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserResourceTest extends ApiTestCase
{
    use ResetDatabase;

    public function testGetUser(): void
    {
        $client = static::createClient();
        $user = UserFactory::new()->createOne();

        $tokenManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        $jwtToken = $tokenManager->create($user);
        $client->withOptions([
            'headers' => [
                'Authorization' => "Bearer {$jwtToken}",
            ],
        ]);
        $client->request('GET', '/api/users/1');

        self::assertResponseIsSuccessful();
        self::assertMatchesResourceItemJsonSchema(UserResource::class);
    }
}
