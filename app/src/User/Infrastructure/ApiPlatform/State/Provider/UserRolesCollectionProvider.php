<?php

namespace App\User\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use App\Shared\Security\GroupPermissions;
use Rekalogika\ApiLite\State\AbstractProvider;

class UserRolesCollectionProvider extends AbstractProvider
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return $this->getGroupedRoles();
    }


}
