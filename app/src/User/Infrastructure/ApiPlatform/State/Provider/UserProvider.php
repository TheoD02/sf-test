<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use App\User\Domain\Security\UserPermissionEnum;
use App\User\Infrastructure\ApiPlatform\Resource\UserResource;
use App\User\Infrastructure\Doctrine\UserRepository;
use Rekalogika\ApiLite\Exception\NotFoundException;
use Rekalogika\ApiLite\State\AbstractProvider;

/**
 * @extends AbstractProvider<UserResource>
 */
class UserProvider extends AbstractProvider
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    #[\Override]
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): UserResource
    {
        $user = $this->userRepository->find($uriVariables['id'] ?? null) ?? throw new NotFoundException();

        $this->denyAccessUnlessGranted(UserPermissionEnum::GET_ONE->value, $user);

        return $this->map(source: $user, target: UserResource::class);
    }
}
