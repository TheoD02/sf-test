<?php

namespace App\Tests\Shared\Security;

use App\Shared\Security\GroupPermissions;
use PHPUnit\Framework\TestCase;

class GroupPermissionsTest extends TestCase
{
    public function testGetPermissions(): void
    {
        foreach (GroupPermissions::cases() as $groupPermission) {
            self::assertIsArray($groupPermission->getPermissions());
            $fqcn = $groupPermission->getFqcn();
            $groupPermissions = $groupPermission->getPermissions();

            foreach ($fqcn::cases() as $permission) {
                self::assertContains($permission->value, $groupPermissions);
            }
        }
    }
}
