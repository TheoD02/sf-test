<?php

namespace App\Battery\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use App\Battery\Domain\Repository\BatteryRepository;
use App\Battery\Infrastructure\ApiPlatform\Output\BatteryStatsPerHourOutput;
use Psr\Log\LoggerAwareTrait;
use Rekalogika\ApiLite\State\AbstractProvider;
use Rekalogika\Mapper\IterableMapperInterface;
use Symfony\Component\Clock\Clock;

/**
 * @extends AbstractProvider<BatteryStatsPerHourOutput>
 */
class BatteryStatsPerHourProvider extends AbstractProvider
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly BatteryRepository       $batteryRepository,
        private readonly IterableMapperInterface $mapper,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $this->logger?->critical('BatteryStatsPerHourProvider::provide');
        return $this->mapper->mapIterable(
            source: $this->batteryRepository->getBatteryStatsPerHour(from: Clock::get()->now()->sub(new \DateInterval('PT10H'))),
            target: BatteryStatsPerHourOutput::class,
        );
    }
}
