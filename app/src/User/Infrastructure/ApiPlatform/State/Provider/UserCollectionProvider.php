<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use App\User\Domain\Security\UserPermissionEnum;
use App\User\Infrastructure\ApiPlatform\Resource\UserResource;
use App\User\Infrastructure\Doctrine\UserRepository;
use Module\ApiPlatformEasyFilter\Trait\FilterApplierTrait;
use Rekalogika\ApiLite\Paginator\MappingPaginatorDecorator;
use Rekalogika\ApiLite\State\AbstractProvider;

/**
 * @extends AbstractProvider<UserResource>
 */
class UserCollectionProvider extends AbstractProvider
{
    use FilterApplierTrait;

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
        array $context = [],
    ): MappingPaginatorDecorator {
        $qb = $this->userRepository->createQueryBuilder('e');

        $this->denyAccessUnlessGranted(UserPermissionEnum::GET_COLLECTION->value, $this->userRepository);

        $this->filterApplierHandler->apply($qb, $operation);

        return $this->mapCollection( // @phpstan-ignore-line return.type (Rekalogika can't provide the correct type)
            collection: $qb,
            target: UserResource::class,
            operation: $operation,
            context: $context,
        );
    }
}
