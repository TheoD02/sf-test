<?php

declare(strict_types=1);

namespace App\Tests\User;

use App\Tests\AbstractApiTestCase;
use App\Tests\Factory\UserFactory;
use App\User\Domain\Security\UserPermissionEnum;
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
        $this->loginAsUser([UserPermissionEnum::GET_ONE]);
        UserFactory::new()->createOne([
            'email' => 'user1@test.test',
        ]);

        // Act
        $this->request('GET', $this->url([
            'id' => 1,
        ]));

        // Assert
        self::assertResponseIsSuccessful();
        self::assertMatchesResourceItemJsonSchema(UserResource::class);
        self::assertPartialResponseContent([
            'email' => 'user1@test.test',
        ]);
    }

    /**
     * @param array{id?: int} $parameters
     */
    #[\Override]
    public function url(array $parameters = []): string
    {
        if (isset($parameters['id'])) {
            return "/api/users/{$parameters['id']}";
        }

        return '/api/users';
    }

    public function testGetCollection(): void
    {
        // Arrange
        $this->loginAsUser([UserPermissionEnum::GET_COLLECTION]);
        $users = UserFactory::new()->createMany(5);

        // Act
        $this->request('GET', $this->url());

        // Assert
        $response = self::getResponse(true);
        self::assertResponseIsSuccessful();
        self::assertMatchesResourceCollectionJsonSchema(UserResource::class);
        self::assertCount(5, $response);

        foreach ($users as $index => $user) {
            /** @var array{email: string} $userResponse */
            $userResponse = $response[$index];
            self::assertSame($user->getEmail(), $userResponse['email']);
        }
    }

    public function testCreateUser(): void
    {
        // Act
        $this->loginAsUser([UserPermissionEnum::CREATE, 'ROLE_ADMIN']);
        $this->request('POST', $this->url(), [
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
        $user = $this->loginAsUser(
            roles: [UserPermissionEnum::UPDATE],
            attributes: [
                'email' => 'old@test.com',
            ],
            persist: true,
        );

        // Act
        $this->request('PATCH', $this->url([
            'id' => $user->getId() ?? 0,
        ]), [
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
        $this->loginAsUser([UserPermissionEnum::DELETE, 'ROLE_ADMIN']);
        UserFactory::new()->createOne();

        // Act
        $this->request('DELETE', $this->url([
            'id' => 1,
        ]));

        // Assert
        self::assertResponseStatusCodeSame(204);
    }
}
