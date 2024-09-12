<?php

namespace App\Tests\Shared\Trait;

use App\Shared\Trait\PermissionTrait;
use PHPUnit\Framework\TestCase;

class PermissionTraitTest extends TestCase
{

    /**
     * @dataProvider getMethodNameData
     */
    public function testGetMethodName(string $value, string $expectedMethodName): void
    {
        // Arrange
        $permission = new class($value) {
            use PermissionTrait;

            public function __construct(public string $value) // Normally is used in enum (replace $this->value by property for testing purposes)
            {
            }
        };

        // Act
        $methodName = $permission->getMethodName();

        // Assert
        self::assertSame($expectedMethodName, $methodName);
    }

    public function getMethodNameData(): \Generator
    {
        yield 'normal' => ['SUPER_NAME', 'canSuperName'];
        yield 'with-space' => ['SUPER NAME', 'canSuperName'];
        yield 'with-dash' => ['SUPER-NAME', 'canSuperName'];
        yield 'with-underscore' => ['SUPER_NAME', 'canSuperName'];
        yield 'with-dot' => ['SUPER.NAME', 'canSuperName'];
        yield 'with-slash' => ['SUPER/NAME', 'canSuperName'];
    }
}
