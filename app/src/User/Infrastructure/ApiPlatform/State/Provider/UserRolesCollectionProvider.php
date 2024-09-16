<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use Rekalogika\ApiLite\State\AbstractProvider;

class UserRolesCollectionProvider extends AbstractProvider
{
    #[\Override]
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return $this->getGroupedRoles();
    }
}
