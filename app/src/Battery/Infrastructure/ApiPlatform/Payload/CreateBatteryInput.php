<?php

namespace App\Battery\Infrastructure\ApiPlatform\Payload;

use Symfony\Component\Validator\Constraints as Assert;

class CreateBatteryInput
{
    #[Assert\PositiveOrZero()]
    public int $level;

    #[Assert\NotBlank()]
    public string $reason;
}
