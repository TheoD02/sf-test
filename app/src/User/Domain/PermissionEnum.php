<?php

declare(strict_types=1);

namespace App\User\Domain;

use ArchTech\Enums\Values;

enum PermissionEnum: string
{
    use Values;

    case GET_ONE = 'USER_GET_ONE';
    case GET_COLLECTION = 'USER_GET_COLLECTION';
    case CREATE = 'USER_CREATE';
    case UPDATE = 'USER_UPDATE';
    case DELETE = 'USER_DELETE';
}
