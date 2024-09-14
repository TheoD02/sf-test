<?php

declare(strict_types=1);

namespace App\Tests\Helper;

use Webmozart\Assert\Assert;

trait GetterSetterTestHelperTrait
{
    /**
     * @var array<string, mixed>
     */
    private array $values = [];

    /**
     * @var ?\ReflectionClass<object>
     */
    private ?\ReflectionClass $reflection = null;

    private ?object $instance = null;

    /**
     * @var array<array-key, array{0: string, 1: string}>
     */
    private array $testableMethods = [];

    /**
     * @param class-string $class
     */
    public function setupObject(string $class): void
    {
        $this->reflection = new \ReflectionClass($class);

        $this->instance = $this
            ->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock()
        ;

        $this->testableMethods = [];
        foreach ($this->reflection->getProperties() as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            $getterName = 'get' . ucfirst($propertyName);
            $setterName = 'set' . ucfirst($propertyName);
            if ($this->reflection->hasMethod($getterName) && $this->reflection->hasMethod($setterName)) {
                $this->testableMethods[] = [$getterName, $setterName];
            }
        }

        $this->values = [];
    }

    public function populateObjectAndAssert(): void
    {
        if ($this->testableMethods === []) {
            // @phpstan-ignore-next-line instanceof.alwaysTrue (false positive, we want to have 1 assert if nothing is set)
            self::assertTrue(true);

            return;
        }

        Assert::notNull($this->reflection);

        foreach ($this->testableMethods as [$getterName, $setterName]) {
            if ($getterName === 'getRoles') {
                continue;
            }

            $refSetter = $this->reflection->getMethod($setterName);
            $refParams = $refSetter->getParameters();
            if (\count($refParams) === 1) {
                $refParam = $refParams[0];
                $expectedValues = [];
                if ($refParam->getType() instanceof \ReflectionNamedType === false) {
                    continue; // Skip complex types for now
                }

                try {
                    $expectedValues[] = $this->getParamMock($refParam->getType());
                } catch (\Throwable) { // @phpstan-ignore-line (false positive, we want to catch all exceptions)
                    continue; // Skip complex types for now
                }

                if ($refParam->allowsNull()) {
                    $expectedValues[] = null;
                }

                foreach ($expectedValues as $expectedValue) {
                    $this->instance->{$setterName}($expectedValue);
                    $actualValue = $this->instance->{$getterName}();

                    $message = \sprintf(
                        'Expected %s() value to equal "%s" (set using %s), got "%s"',
                        $getterName,
                        // @phpstan-ignore-next-line ekinoBannedCode.function (OK, for this case)
                        print_r($expectedValue, true),
                        $setterName,
                        // @phpstan-ignore-next-line ekinoBannedCode.function (OK, for this case)
                        print_r($actualValue, true),
                    );

                    $this->assertEquals($expectedValue, $actualValue, $message);
                }
            }
        }
    }

    private function getParamMock(\ReflectionType $refType): mixed
    {
        Assert::isInstanceOf($refType, \ReflectionNamedType::class);
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
            // @phpstan-ignore-next-line (OK, for this case)
            default => $this->getMockBuilder($type)->disableOriginalConstructor()->getMock(),
        };
    }
}
