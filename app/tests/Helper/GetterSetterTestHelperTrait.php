<?php

namespace App\Tests\Helper;

use Random\RandomException;
use function Zenstruck\Foundry\faker;

trait GetterSetterTestHelperTrait
{
    /**
     * @param array<string, mixed> $values
     */
    private array $values = [];

    private \ReflectionClass $reflection;

    private object $instance;

    private array $testableMethods = [];

    /**
     * @param class-string $class
     *
     * @throws \ReflectionException
     */
    protected function setupObject(string $class): void
    {
        $this->reflection = new \ReflectionClass($class);

        $this->instance = $this
            ->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $this->testableMethods = [];
        foreach ($this->reflection->getProperties() as $refProperty) {
            $propertyName = $refProperty->getName();
            $getterName = 'get' . ucfirst($propertyName);
            $setterName = 'set' . ucfirst($propertyName);
            if ($this->reflection->hasMethod($getterName) && $this->reflection->hasMethod($setterName)) {
                $this->testableMethods[] = [$getterName, $setterName];
            }
        }

        $this->values = [];
    }

    /**
     * @throws RandomException
     * @throws \ReflectionException
     */
    protected function populateObjectAndAssert(): void
    {
        if ($this->testableMethods === []) {
            self::assertTrue(true);
            return;
        }

        foreach ($this->testableMethods as [$getterName, $setterName]) {
            if ($getterName === 'getRoles') {
                continue;
            }
            $refSetter = $this->reflection->getMethod($setterName);
            $refParams = $refSetter->getParameters();
            if (1 === count($refParams)) {
                $refParam = $refParams[0];
                $expectedValues = [];
                if ($refParam->getType() instanceof \ReflectionNamedType === false) {
                    continue; // Skip complex types for now
                }
                try {
                    $expectedValues[] = $this->getParamMock($refParam->getType());
                } catch (\Throwable $e) {
                    continue; // Skip complex types for now
                }
                if ($refParam->allowsNull() && null !== $refParam->getType()) {
                    $expectedValues[] = null;
                }
                foreach ($expectedValues as $expectedValue) {
                    $this->instance->{$setterName}($expectedValue);
                    $actualValue = $this->instance->{$getterName}();

                    $message = sprintf(
                        'Expected %s() value to equal "%s" (set using %s), got "%s"',
                        $getterName,
                        print_r($expectedValue, 1),
                        $setterName,
                        print_r($actualValue, 1),
                    );

                    $this->assertEquals($expectedValue, $actualValue, $message);
                }
            }
        }
    }

    /**
     * @throws RandomException
     */
    private function getParamMock(\ReflectionType|\ReflectionIntersectionType|\ReflectionNamedType|\ReflectionUnionType $refType): mixed
    {
        $type = $refType->getName();

        if (interface_exists($type)) {
            return $this->getMockBuilder($type)->getMockForAbstractClass();
        }

        return match ($type) {
            'NULL' => 'null',
            'boolean' => (bool) random_int(0, 1),
            'integer' => random_int(1, 100),
            'string' => str_shuffle('abcdefghijklmnopqrstuvxyz0123456789'),
            'array' => [],
            default => $this->getMockBuilder($refType)->disableOriginalConstructor()->getMock(),
        };
    }
}
