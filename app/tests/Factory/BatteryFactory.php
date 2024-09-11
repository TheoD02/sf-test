<?php

namespace App\Tests\Factory;

use App\Battery\Domain\Model\Battery;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Battery>
 */
final class BatteryFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Battery::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'level' => self::faker()->numberBetween(1, 100),
            'reason' => self::faker()->randomElement(['automatic_report', 'hour_report', 'open_app', 'close_app']),
            'recordedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween(startDate: '-3 day', endDate: 'now', timezone: 'Europe/Paris')),
        ];
    }
}
