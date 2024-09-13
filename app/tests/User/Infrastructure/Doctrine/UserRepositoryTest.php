<?php

declare(strict_types=1);

namespace App\Tests\User\Infrastructure\Doctrine;

use App\Tests\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @internal
 */
final class UserRepositoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    public function testUpgradePassword(): void
    {
        // Arrange
        $user = UserFactory::new()->create([
            'password' => 'unknown',
        ]);
        $newPassword = '$2y$13$2tqYsgWC3r/xtFMipQCvt.m1aJ4uvfjk4ng8dYW50SlGdiLCWgtT2';

        // Act
        // @phpstan-ignore-next-line method.notFound (This one exists only in doctrine but is only the one declared for now)
        UserFactory::repository()->upgradePassword($user->_real(), $newPassword);

        // Assert
        self::assertSame($newPassword, $user->getPassword());
    }
}
