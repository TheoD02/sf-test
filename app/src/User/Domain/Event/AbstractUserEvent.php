<?php

namespace App\User\Domain\Event;

class AbstractUserEvent
{
    public function __construct(
        public readonly int $id,
    )
    {
    }

    public function getId(): int
    {
        return $this->id;
    }
}
