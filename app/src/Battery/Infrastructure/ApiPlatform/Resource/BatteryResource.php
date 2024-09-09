<?php

namespace App\Battery\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Battery\Infrastructure\ApiPlatform\Payload\CreateBatteryInput;
use App\Battery\Infrastructure\ApiPlatform\State\Processor\BatteryPostProcessor;
use App\Battery\Infrastructure\ApiPlatform\State\Provider\BatteryCollectionProvider;

#[ApiResource(
    shortName: 'Battery',
    operations: [
        new GetCollection(provider: BatteryCollectionProvider::class),
        new Post(input: CreateBatteryInput::class, processor: BatteryPostProcessor::class),
    ]
)]
class BatteryResource
{
    private ?int $id = null;

    private ?int $level = null;

    private ?\DateTimeImmutable $recordedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): BatteryResource
    {
        $this->id = $id;
        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(?int $level): BatteryResource
    {
        $this->level = $level;
        return $this;
    }

    public function getRecordedAt(): ?\DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function setRecordedAt(?\DateTimeImmutable $recordedAt): BatteryResource
    {
        $this->recordedAt = $recordedAt;
        return $this;
    }
}
