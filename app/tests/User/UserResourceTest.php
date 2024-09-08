<?php

declare(strict_types=1);

namespace App\Tests\User;

use App\Tests\AbstractApiTestCase;
use App\Tests\Factory\UserFactory;
use App\User\Domain\PermissionEnum;
use App\User\Infrastructure\ApiPlatform\Resource\UserResource;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @internal
 */
final class UserResourceTest extends AbstractApiTestCase
{
    use Factories;
    use ResetDatabase;

    public function testGetUser(): void
    {
        // Arrange
        $this->loginAsUser([PermissionEnum::GET_ONE]);
        UserFactory::new()->createOne([
            'email' => 'user1@test.test',
        ]);

        // Act
        $this->request('GET', '/api/users/1');

        // Assert
        self::assertResponseIsSuccessful();
        self::assertMatchesResourceItemJsonSchema(UserResource::class);
        self::assertPartialResponseContent([
            'email' => 'user1@test.test',
        ]);
    }

    public function testGetCollection(): void
    {
        // Arrange
        $this->loginAsUser([PermissionEnum::GET_COLLECTION]);
        $users = UserFactory::new()->createMany(5);

        // Act
        $this->request('GET', '/api/users');

        // Assert
        $response = self::getResponse(true);
        self::assertResponseIsSuccessful();
        self::assertMatchesResourceCollectionJsonSchema(UserResource::class);
        self::assertCount(5, $response);

        foreach ($users as $index => $user) {
            $userResponse = $response[$index];
            self::assertSame($user->getEmail(), $userResponse['email']);
        }
    }

    public function testCreateUser(): void
    {
        // Act
        $this->loginAsUser([PermissionEnum::CREATE]);
        $this->request('POST', '/api/users', [
            'json' => [
                'email' => 'test@test.com',
                'password' => 'test',
                'roles' => ['ROLE_USER'],
            ],
        ]);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertMatchesResourceItemJsonSchema(UserResource::class);
    }

    public function testUpdateUser(): void
    {
        // Arrange
        $this->loginAsUser([PermissionEnum::UPDATE]);
        UserFactory::new()->createOne([
            'email' => 'old@test.com',
        ]);

        // Act
        $this->request('PATCH', '/api/users/1', [
            'json' => [
                'email' => 'new@test.com',
                'password' => 'test',
                'roles' => ['ROLE_USER'],
            ],
        ]);

        // Assert
        self::assertResponseIsSuccessful();
        self::assertMatchesResourceItemJsonSchema(UserResource::class);
        self::assertPartialResponseContent([
            'email' => 'new@test.com',
        ]);
    }

    public function testDeleteUser(): void
    {
        // Arrange
        $this->loginAsUser([PermissionEnum::DELETE, 'ROLE_ADMIN']);
        UserFactory::new()->createOne();

        // Act
        $this->request('DELETE', '/api/users/1');

        // Assert
        self::assertResponseStatusCodeSame(204);
    }
}
