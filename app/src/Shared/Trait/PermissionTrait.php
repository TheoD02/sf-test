<?php

declare(strict_types=1);

namespace App\Shared\Trait;

use function Symfony\Component\String\u;

trait PermissionTrait
{
    public function getMethodName(): string
    {
        return 'can' . ucfirst(u($this->value)->lower()->camel()->toString());
    }
}
