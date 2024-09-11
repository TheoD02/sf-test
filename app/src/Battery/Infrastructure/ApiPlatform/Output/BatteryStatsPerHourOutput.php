<?php

namespace App\Battery\Infrastructure\ApiPlatform\Output;

class BatteryStatsPerHourOutput
{
    public \DateTimeImmutable $hour;
    public int $levelAtStart = 0;
    public int $levelAtEnd = 0;
    public int $recordCount = 0;

    public function getLevelChange(): int
    {
        return $this->levelAtEnd - $this->levelAtStart;
    }
}
