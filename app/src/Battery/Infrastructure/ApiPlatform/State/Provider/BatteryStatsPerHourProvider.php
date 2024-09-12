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

    #[\Override]
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = $context['request'] ?? null;
        Assert::isInstanceOf($request, Request::class);

        $from = $request->query->get('from');
        $to = $request->query->get('to');

        $range = $request->query->get('range', 'hour');
        if (! \in_array($range, ['hour', 'tenMinute'], true)) {
            throw new BadRequestHttpException('Invalid range should be hour or tenMinute');
        }

        if ($from !== null) {
            $from = Carbon::parse($from)->toDateTimeImmutable();
        }

        if ($to !== null) {
            $to = Carbon::parse($to)->toDateTimeImmutable();
        }

        if (!$from instanceof \DateTimeImmutable && !$to instanceof \DateTimeImmutable) {
            $from = Clock::get()->now()->sub(new \DateInterval('PT10H'));
            $to = Clock::get()->now()->add(new \DateInterval('PT10H'));
        }

        $source = match ($range) {
            'hour' => $this->batteryRepository->getBatteryStatsPerHourRawSql(from: $from, to: $to),
            'tenMinute' => $this->batteryRepository->getBatteryStatsPerTenMinutesRawSql(from: $from, to: $to),
            default => throw new BadRequestHttpException('Invalid range should be hour or tenMinute'),
        };

        return $this->mapper->mapIterable(source: $source, target: BatteryStatsPerHourOutput::class);
    }
}
