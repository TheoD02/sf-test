<?php

namespace App\User\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use App\User\Domain\Model\User;
use App\User\Infrastructure\ApiPlatform\Resource\UserResource;
use Rekalogika\ApiLite\State\AbstractProvider;
use Symfony\Bundle\SecurityBundle\Security;

class UserMeProvider extends AbstractProvider
{
    public function __construct(
        private readonly Security $security,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $user = $this->security->getUser();

        if (! $user instanceof User) {
            return null;
        }

        return $this->map($user, UserResource::class);
    }
}
