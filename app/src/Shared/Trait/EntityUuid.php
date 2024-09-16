<?php

declare(strict_types=1);

namespace App\Shared\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

trait EntityUuid
{
    #[ORM\Column(type: UuidType::NAME)]
    private Uuid $uuid;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }
}
