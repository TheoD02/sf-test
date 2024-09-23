<?php

namespace App\Battery\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use App\Battery\Domain\Repository\BatteryRepository;
use App\Battery\Infrastructure\ApiPlatform\Resource\BatteryResource;
use Rekalogika\ApiLite\State\AbstractProvider;
use Rekalogika\ApiLite\Paginator\MappingPaginatorDecorator;

/**
 * @extends AbstractProvider<BatteryResource>
 */
class BatteryCollectionProvider extends AbstractProvider
{
    public function __construct(
        private readonly BatteryRepository $batteryRepository,
    )
    {
    }

    /**
     * @return MappingPaginatorDecorator<BatteryResource>
     */
    #[\Override]
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object
    {
        $qb = $this->batteryRepository->createQueryBuilder('b')
            ->orderBy('b.recordedAt', 'DESC');

        return $this->mapCollection(
            collection: $qb,
            target: BatteryResource::class,
            operation: $operation,
            context: $context,
        );
    }
}
