<?php

declare(strict_types=1);

namespace App\Battery\Infrastructure\ApiPlatform\State\Provider;

use ApiPlatform\Metadata\Operation;
use App\Battery\Domain\Repository\BatteryRepository;
use App\Battery\Infrastructure\ApiPlatform\Output\BatteryStatsPerHourOutput;
use Carbon\Carbon;
use Rekalogika\ApiLite\State\AbstractProvider;
use Rekalogika\Mapper\IterableMapperInterface;
use Symfony\Component\Clock\Clock;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webmozart\Assert\Assert;

/**
 * @extends AbstractProvider<BatteryStatsPerHourOutput>
 */
class BatteryStatsPerHourProvider extends AbstractProvider
{
    public function __construct(
        private readonly BatteryRepository $batteryRepository,
        private readonly IterableMapperInterface $mapper,
    ) {
    }

    /**
     * @return \Generator<BatteryStatsPerHourOutput>
     */
    #[\Override]
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): \Generator
    {
        $request = $context['request'] ?? null;
        Assert::isInstanceOf($request, Request::class);

        $fromQuery = $request->query->get('from');
        $toQuery = $request->query->get('to');

        $range = $request->query->get('range', 'hour');
        if (! \in_array($range, ['hour', 'tenMinute'], true)) {
            throw new BadRequestHttpException('Invalid range should be hour or tenMinute');
        }

        $from = null;
        $to = null;
        if ($fromQuery !== null) {
            $from = Carbon::parse($fromQuery)->toDateTimeImmutable();
        }

        if ($toQuery !== null) {
            $to = Carbon::parse($toQuery)->toDateTimeImmutable();
        }

        if (! $from instanceof \DateTimeImmutable && ! $to instanceof \DateTimeImmutable) {
            $from = Clock::get()->now()->sub(new \DateInterval('PT10H'));
            $to = Clock::get()->now()->add(new \DateInterval('PT10H'));
        }

        $source = match ($range) {
            'hour' => $this->batteryRepository->getBatteryStatsPerHourRawSql(from: $from, to: $to),
            'tenMinute' => $this->batteryRepository->getBatteryStatsPerTenMinutesRawSql(from: $from, to: $to),
        };

        /** @var \Generator<BatteryStatsPerHourOutput> */
        return $this->mapper->mapIterable(source: $source, target: BatteryStatsPerHourOutput::class);
    }
}
