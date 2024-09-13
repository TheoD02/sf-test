<?php

declare(strict_types=1);

namespace App\Tests\Shared\Trait;

use App\Shared\Trait\PermissionTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class PermissionTraitTest extends TestCase
{
    /**
     * @dataProvider provideGetMethodNameCases
     */
    public function testGetMethodName(string $value, string $expectedMethodName): void
    {
        // Arrange
        $permission = new class($value) {
            use PermissionTrait;

            public function __construct(
                // Normally is used in enum (replace $this->value by property for testing purposes)
                public string $value,
            ) {
            }
        };

        // Act
        $methodName = $permission->getMethodName();

        // Assert
        self::assertSame($expectedMethodName, $methodName);
    }

    /**
     * @return iterable<string, array{value: string, expectedMethodName: string}>
     */
    public function provideGetMethodNameCases(): iterable
    {
        yield 'normal' => [
            'value' => 'SUPER_NAME',
            'expectedMethodName' => 'canSuperName',
        ];
        yield 'normal-with-space' => [
            'value' => 'SUPER NAME',
            'expectedMethodName' => 'canSuperName',
        ];
        yield 'normal-with-underscore' => [
            'value' => 'SUPER_NAME',
            'expectedMethodName' => 'canSuperName',
        ];
        yield 'normal-with-dash' => [
            'value' => 'SUPER-NAME',
            'expectedMethodName' => 'canSuperName',
        ];
        yield 'normal-with-dot' => [
            'value' => 'SUPER.NAME',
            'expectedMethodName' => 'canSuperName',
        ];
        yield 'normal-with-dot-space' => [
            'value' => 'SUPER. NAME',
            'expectedMethodName' => 'canSuperName',
        ];
        yield 'normal-with-dot-underscore' => [
            'value' => 'SUPER.NAME',
            'expectedMethodName' => 'canSuperName',
        ];
        yield 'normal-with-dot-dash' => [
            'value' => 'SUPER.-NAME',
            'expectedMethodName' => 'canSuperName',
        ];
        yield 'normal-with-dot-dot' => [
            'value' => 'SUPER..NAME',
            'expectedMethodName' => 'canSuperName',
        ];
        yield 'normal-with-dot-dot-space' => [
            'value' => 'SUPER.. NAME',
            'expectedMethodName' => 'canSuperName',
        ];
        yield 'normal-with-dot-dot-underscore' => [
            'value' => 'SUPER..NAME',
            'expectedMethodName' => 'canSuperName',
        ];
        yield 'normal-with-dot-dot-dash' => [
            'value' => 'SUPER..-NAME',
            'expectedMethodName' => 'canSuperName',
        ];
    }
}
