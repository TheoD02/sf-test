<?php

declare(strict_types=1);

namespace App\Battery\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use App\Battery\Domain\Repository\BatteryRepository;
use App\Battery\Infrastructure\ApiPlatform\Resource\BatteryResource;
use Rekalogika\ApiLite\Paginator\MappingPaginatorDecorator;
use Rekalogika\ApiLite\State\AbstractProvider;

/**
 * @extends AbstractProvider<BatteryResource>
 */
class BatteryCollectionProvider extends AbstractProvider
{
    public function __construct(
        private readonly BatteryRepository $batteryRepository,
    ) {
    }

    /**
     * @return MappingPaginatorDecorator<BatteryResource>
     */
    #[\Override]
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object
    {
        return $this->mapCollection(
            collection: $this->batteryRepository,
            target: BatteryResource::class,
            operation: $operation,
            context: $context,
        );
    }
}
