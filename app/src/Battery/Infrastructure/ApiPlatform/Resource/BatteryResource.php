<?php

declare(strict_types=1);

namespace App\Battery\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Battery\Infrastructure\ApiPlatform\Output\BatteryStatsPerHourOutput;
use App\Battery\Infrastructure\ApiPlatform\Payload\CreateBatteryInput;
use App\Battery\Infrastructure\ApiPlatform\State\Processor\BatteryPostProcessor;
use App\Battery\Infrastructure\ApiPlatform\State\Provider\BatteryCollectionProvider;
use App\Battery\Infrastructure\ApiPlatform\State\Provider\BatteryStatsPerHourProvider;

#[ApiResource(
    shortName: 'Battery',
    operations: [
        new GetCollection(provider: BatteryCollectionProvider::class),
        new Get(
            uriTemplate: '/batteries/stats/per-hour',
            output: BatteryStatsPerHourOutput::class,
            provider: BatteryStatsPerHourProvider::class
        ),
        new Post(input: CreateBatteryInput::class, processor: BatteryPostProcessor::class),
    ]
)]
class BatteryResource
{
    private ?int $id = null;

    private ?int $level = null;

    private ?string $reason = null;

    private ?\DateTimeImmutable $recordedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getRecordedAt(): ?\DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function setRecordedAt(?\DateTimeImmutable $recordedAt): self
    {
        $this->recordedAt = $recordedAt;

        return $this;
    }
}
