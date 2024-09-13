<?php

declare(strict_types=1);

namespace App\User\Domain\Security;

use App\Shared\Trait\PermissionTrait;
use ArchTech\Enums\Values;

enum UserPermissionEnum: string
{
    use PermissionTrait;
    use Values;

    case GET_ONE = 'USER_GET_ONE';
    case GET_COLLECTION = 'USER_GET_COLLECTION';
    case CREATE = 'USER_CREATE';
    case UPDATE = 'USER_UPDATE';
    case DELETE = 'USER_DELETE';
}
