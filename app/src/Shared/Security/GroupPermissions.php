<?php

namespace App\Shared\Security;

use App\User\Domain\Security\UserPermissionEnum;

enum GroupPermissions: string
{
    case USER_PERMISSIONS = 'user';

    public function getPermissions(): array
    {
        return match ($this) {
            self::USER_PERMISSIONS => UserPermissionEnum::values(),
        };
    }

    public function getFqcn(): string
    {
        return match ($this) {
            self::USER_PERMISSIONS => UserPermissionEnum::class,
        };
    }
}
