<?php

namespace App\Battery\Domain\Model;

use App\Battery\Domain\Repository\BatteryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Clock\Clock;

#[ORM\Entity(repositoryClass: BatteryRepository::class)]
#[ORM\Table(name: '`battery`')]
class Battery
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $level = 0;

    #[ORM\Column()]
    private string $reason = '';

    /**
     * @var array<int, array{type: 'cellular', operator: string, radio: string, level: int}|array{type: 'wifi', ssid: string, level: int}>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $data = [];

    #[ORM\Column]
    private \DateTimeImmutable $recordedAt;

    public function __construct()
    {
        $this->recordedAt = Clock::get()->withTimeZone('Europe/Paris')->now();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    public function getRecordedAt(): \DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function setRecordedAt(\DateTimeImmutable $recordedAt): static
    {
        $this->recordedAt = $recordedAt;

        return $this;
    }

    /**
     * @return array<int, array{type: 'cellular', operator: string, radio: string, level: int}|array{type: 'wifi', ssid: string, level: int}>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<int, array{type: 'cellular', operator: string, radio: string, level: int}|array{type: 'wifi', ssid: string, level: int}> $data
     * @return $this
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
