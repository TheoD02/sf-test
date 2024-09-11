<?php

namespace App\Battery\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use App\Battery\Domain\Repository\BatteryRepository;
use App\Battery\Infrastructure\ApiPlatform\Output\BatteryStatsPerHourOutput;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Rekalogika\ApiLite\State\AbstractProvider;
use Rekalogika\Mapper\IterableMapperInterface;
use Symfony\Component\Clock\Clock;

/**
 * @extends AbstractProvider<BatteryStatsPerHourOutput>
 */
class BatteryStatsPerHourProvider extends AbstractProvider
{
    public function __construct(
        private readonly BatteryRepository       $batteryRepository,
        private readonly IterableMapperInterface $mapper,
        private readonly LoggerInterface         $logger,
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $this->logger?->critical('Coucou le log !', ['that freaking thing' => 123]);
        return $this->mapper->mapIterable(
            source: $this->batteryRepository->getBatteryStatsPerHour(from: Clock::get()->now()->sub(new \DateInterval('PT10H'))),
            target: BatteryStatsPerHourOutput::class,
        );
    }
}
