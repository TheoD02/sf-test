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
final class UserCollectionProviderTest extends AbstractApiTestCase
{
    public function testProvide(): void
    {
        // Arrange
        $this->loginAsUser([UserPermissionEnum::GET_COLLECTION]);
        UserFactory::new()->many(5)->create();

        // Act
        $this->request('GET', $this->url());

        // Assert
        self::assertResponseStatusCodeSame(200);
        self::assertMatchesResourceCollectionJsonSchema(UserResource::class);
        self::assertCount(5, self::getResponse(true));
    }

    #[\Override]
    public function url(array $parameters = []): string
    {
        return '/api/users';
    }
}
