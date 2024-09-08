<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use App\User\Domain\PermissionEnum;
use App\User\Domain\Repository\UserRepository;
use App\User\Infrastructure\ApiPlatform\Resource\UserResource;
use Rekalogika\ApiLite\Paginator\MappingPaginatorDecorator;
use Rekalogika\ApiLite\State\AbstractProvider;

/**
 * @extends AbstractProvider<UserResource>
 */
class UserCollectionProvider extends AbstractProvider
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * @return MappingPaginatorDecorator<UserResource>
     */
    #[\Override]
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): MappingPaginatorDecorator {
        $this->denyAccessUnlessGranted(PermissionEnum::GET_COLLECTION->value, $this->userRepository);

        return $this->mapCollection( // @phpstan-ignore-line return.type (Rekalogika can't provide the correct type)
            $this->userRepository,
            target: UserResource::class,
            operation: $operation,
            context: $context
        );
    }
}
