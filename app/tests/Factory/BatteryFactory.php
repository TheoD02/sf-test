<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Battery\Domain\Model\Battery;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Battery>
 */
final class BatteryFactory extends PersistentProxyObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Battery::class;
    }

    #[\Override]
    protected function defaults(): array
    {
        return [
            'level' => self::faker()->numberBetween(1, 100),
            'reason' => self::faker()->randomElement(['automatic_report', 'hour_report', 'open_app', 'close_app']),
            'recordedAt' => \DateTimeImmutable::createFromMutable(
                self::faker()->dateTimeBetween(startDate: '-3 day', endDate: 'now', timezone: 'Europe/Paris'),
            ),
        ];
    }
}
