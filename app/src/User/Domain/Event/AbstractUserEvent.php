<?php

declare(strict_types=1);

namespace App\User\Domain\Event;

use Symfony\Component\Uid\Uuid;

class AbstractUserEvent
{
    public function __construct(
        public readonly Uuid $uuid,
    ) {
    }

    public function getUuid(): Uuid
    {
        return $this->uuid;
    }
}
