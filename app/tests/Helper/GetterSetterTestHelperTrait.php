<?php

namespace App\Tests\Helper;

use function Zenstruck\Foundry\faker;

trait GetterSetterTestHelperTrait
{
    /**
     * @param array<string, mixed> $values
     */
    private array $values = [];

    private \ReflectionClass $reflection;

    private object $instance;

    protected function setupObject(object $object): void
    {
        $this->reflection = new \ReflectionClass($object::class);
        $this->instance = $object;
        $this->values = [];
    }

    protected function populateObject(): void
    {
        foreach ($this->reflection->getProperties() as $property) {
            $type = $property->getType();

            if ($type->isBuiltin() === false) {
                continue;
            }


            $value = $this->getFakerFromBuiltinType($type->getName());
            $property->setValue($this->instance, $value);

            $this->values[$property->getName()] = $value;
        }
    }

    private function getFakerFromBuiltinType(string $type): mixed
    {
        return match ($type) {
            'string' => faker()->text(),
            'int' => faker()->numberBetween(),
            'float' => faker()->randomFloat(),
            'bool' => faker()->boolean(),
            'array' => faker()->randomElements(),
            default => throw new \LogicException('Type not supported'),
        };
    }

    protected function assertObject(): void
    {
        foreach ($this->reflection->getProperties() as $property) {
            $type = $property->getType();

            if ($type->isBuiltin() === false) {
                continue;
            }

            self::assertSame($this->values[$property->getName()], $property->getValue($this->instance));
        }
    }
}
