<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\Payload;

class PatchUserInput
{
    public string $email;

    public string $password;

    /**
     * @var non-empty-list<string>
     */
    public array $roles;
}
