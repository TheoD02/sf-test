<?php

declare(strict_types=1);

namespace Module\ApiPlatformEasyFilter\Filter\Definition;

use Module\ApiPlatformEasyFilter\Filter\Operator\Adapter\OperatorInterface;
use Module\ApiPlatformEasyFilter\Filter\Operator\ContainsOperator;
use Module\ApiPlatformEasyFilter\Filter\Operator\EndsWithOperator;
use Module\ApiPlatformEasyFilter\Filter\Operator\EqualsOperator;
use Module\ApiPlatformEasyFilter\Filter\Operator\GreaterThanOperator;
use Module\ApiPlatformEasyFilter\Filter\Operator\GreaterThanOrEqualsOperator;
use Module\ApiPlatformEasyFilter\Filter\Operator\InOperator;
use Module\ApiPlatformEasyFilter\Filter\Operator\IsNotNullOperator;
use Module\ApiPlatformEasyFilter\Filter\Operator\IsNullOperator;
use Module\ApiPlatformEasyFilter\Filter\Operator\LessThanOperator;
use Module\ApiPlatformEasyFilter\Filter\Operator\LessThanOrEqualsOperator;
use Module\ApiPlatformEasyFilter\Filter\Operator\NotEqualsOperator;
use Module\ApiPlatformEasyFilter\Filter\Operator\NotInOperator;
use Module\ApiPlatformEasyFilter\Filter\Operator\StartsWithOperator;

class FilterDefinition
{
    private string $field = '';

    /**
     * @var array<OperatorInterface>
     */
    private array $operators = [];

    private function __construct()
    {
    }

    /**
     * @param array<OperatorInterface> $operators
     */
    public static function create(string $field, array $operators = []): self
    {
        return (new self())
            ->setField($field)
            ->operators($operators);
    }

    /**
     * @param array<OperatorInterface> $operators
     */
    public function operators(array $operators): self
    {
        $this->operators = $operators;

        return $this;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $field): self
    {
        $this->field = $field;

        return $this;
    }

    public function getName(): string
    {
        return $this->field;
    }

    /**
     * @return array<OperatorInterface>
     */
    public function getOperators(): array
    {
        return $this->operators;
    }

    public function getOperator(string $name): OperatorInterface
    {
        return $this->operators[$name];
    }

    public function hasOperator(string $name): bool
    {
        return isset($this->operators[$name]);
    }

    public function addOperators(string ...$operatorFqcns): self
    {
        foreach ($operatorFqcns as $operatorFqcn) {
            $this->addOperator($operatorFqcn);
        }

        return $this;
    }

    public function addOperator(string $operatorFqcn): self
    {
        $operator = $this->instantiateOperator($operatorFqcn);

        $this->operators[$operator->queryOperatorName()] = $operator;

        return $this;
    }

    private function instantiateOperator(string $operatorFqcn): OperatorInterface
    {
        $operator = new $operatorFqcn();
        if (! $operator instanceof OperatorInterface) {
            throw new \InvalidArgumentException(sprintf('Operator must implement %s', OperatorInterface::class));
        }

        return $operator;
    }

    /**
     * @param array<string> $exclude
     */
    public function addStringOperators(array $exclude = []): self
    {
        $this->addOperator(ContainsOperator::class);
        $this->addOperator(EndsWithOperator::class);
        $this->addOperator(StartsWithOperator::class);
        $this->addOperator(EqualsOperator::class);
        $this->addOperator(NotEqualsOperator::class);
        $this->addOperator(InOperator::class);
        $this->addOperator(NotInOperator::class);
        $this->addOperator(IsNullOperator::class);
        $this->addOperator(IsNotNullOperator::class);

        foreach ($exclude as $operator) {
            $this->removeOperator($operator);
        }

        return $this;
    }

    public function removeOperator(string $name): self
    {
        unset($this->operators[$name]);

        return $this;
    }

    /**
     * @param array<string> $exclude
     */
    public function addNumericOperators(array $exclude = []): self
    {
        $this->addOperator(EqualsOperator::class);
        $this->addOperator(NotEqualsOperator::class);
        $this->addOperator(GreaterThanOperator::class);
        $this->addOperator(GreaterThanOrEqualsOperator::class);
        $this->addOperator(LessThanOperator::class);
        $this->addOperator(LessThanOrEqualsOperator::class);
        $this->addOperator(InOperator::class);
        $this->addOperator(NotInOperator::class);
        $this->addOperator(IsNullOperator::class);
        $this->addOperator(IsNotNullOperator::class);

        foreach ($exclude as $operator) {
            $this->removeOperator($operator);
        }

        return $this;
    }

    /**
     * @param array<string> $exclude
     */
    public function addBooleanOperators(array $exclude = []): self
    {
        $this->addOperator(EqualsOperator::class);
        $this->addOperator(NotEqualsOperator::class);
        $this->addOperator(IsNullOperator::class);
        $this->addOperator(IsNotNullOperator::class);

        foreach ($exclude as $operator) {
            $this->removeOperator($operator);
        }

        return $this;
    }

    /**
     * @param array<string> $exclude
     */
    public function addDateTimeOperators(array $exclude = []): self
    {
        $this->addOperator(EqualsOperator::class);
        $this->addOperator(NotEqualsOperator::class);
        $this->addOperator(GreaterThanOperator::class);
        $this->addOperator(GreaterThanOrEqualsOperator::class);
        $this->addOperator(LessThanOperator::class);
        $this->addOperator(LessThanOrEqualsOperator::class);
        $this->addOperator(InOperator::class);
        $this->addOperator(NotInOperator::class);
        $this->addOperator(IsNullOperator::class);
        $this->addOperator(IsNotNullOperator::class);

        foreach ($exclude as $operator) {
            $this->removeOperator($operator);
        }

        return $this;
    }

    /**
     * @param array<string> $exclude
     */
    public function addArrayOperators(array $exclude = []): self
    {
        $this->addOperator(ContainsOperator::class);
        $this->addOperator(EqualsOperator::class);
        $this->addOperator(NotEqualsOperator::class);
        $this->addOperator(InOperator::class);
        $this->addOperator(NotInOperator::class);
        $this->addOperator(IsNullOperator::class);
        $this->addOperator(IsNotNullOperator::class);

        foreach ($exclude as $operator) {
            $this->removeOperator($operator);
        }

        return $this;
    }
}
