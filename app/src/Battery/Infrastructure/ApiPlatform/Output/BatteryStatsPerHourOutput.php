<?php

declare(strict_types=1);

namespace App\Battery\Infrastructure\ApiPlatform\Output;

use Carbon\Carbon;

class BatteryStatsPerHourOutput
{
    public \DateTimeImmutable $hour;

    public int $levelAtStart = 0;

    public int $levelAtEnd = 0;

    public int $recordCount = 0;

    public function __construct()
    {
        $this->hour = Carbon::now()->toDateTimeImmutable();
    }

    public function getLevelChange(): int
    {
        return $this->levelAtEnd - $this->levelAtStart;
    }
}
