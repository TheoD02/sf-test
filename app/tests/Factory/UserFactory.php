<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\User\Domain\Model\User;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return User::class;
    }

    #[\Override]
    protected function defaults(): array
    {
        return [
            'email' => self::faker()->email(),
            'password' => '$2y$13$2tqYsgWC3r/xtFMipQCvt.m1aJ4uvfjk4ng8dYW50SlGdiLCWgtT2', // admin
            'roles' => ['ROLE_USER'],
        ];
    }
}
