<?php

declare(strict_types=1);

namespace App\Tests\Shared\Security;

use App\Shared\Security\GroupPermissions;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class GroupPermissionsTest extends TestCase
{
    public function testGetPermissions(): void
    {
        foreach (GroupPermissions::cases() as $groupPermission) {
            $fqcn = $groupPermission->getFqcn();
            $groupPermissions = $groupPermission->getPermissions();
            /** @var array<\BackedEnum> $cases */
            $cases = $fqcn::cases();

            foreach ($cases as $case) {
                self::assertContains($case->value, $groupPermissions);
            }
        }
    }
}
