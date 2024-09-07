<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\Payload;

class CreateUserInput
{
    public string $email;

    public string $password;
}
