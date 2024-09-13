<?php

declare(strict_types=1);

namespace App\Shared\Security;

use App\User\Domain\Security\UserPermissionEnum;

enum GroupPermissions: string
{
    case USER_PERMISSIONS = 'user';

    /**
     * @return array<string>
     */
    public function getPermissions(): array
    {
        /** @var array<string> */
        return match ($this) {
            self::USER_PERMISSIONS => UserPermissionEnum::values(), // @phpstan-ignore-line (authorized in enum please)
        };
    }

    /**
     * @return class-string
     */
    public function getFqcn(): string
    {
        return match ($this) {
            self::USER_PERMISSIONS => UserPermissionEnum::class, // @phpstan-ignore-line (authorized in enum please)
        };
    }
}
