<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use App\User\Domain\Model\User;
use App\User\Infrastructure\ApiPlatform\Resource\UserResource;
use Rekalogika\ApiLite\State\AbstractProvider;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends AbstractProvider<UserResource>
 */
class UserMeProvider extends AbstractProvider
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    #[\Override]
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?UserResource
    {
        $user = $this->security->getUser();

        if (! $user instanceof User) {
            return null;
        }

        return $this->map($user, UserResource::class);
    }
}
