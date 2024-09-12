<?php

declare(strict_types=1);

namespace Module\ApiPlatformEasyFilter\Filter\Operator;

use Module\ApiPlatformEasyFilter\Filter\Definition\FilterDefinition;
use Module\ApiPlatformEasyFilter\Filter\Operator\Adapter\ArrayOperatorInterface;
use Module\ApiPlatformEasyFilter\Filter\Operator\Adapter\NumberOperatorInterface;
use Module\ApiPlatformEasyFilter\Filter\Operator\Adapter\OrmOperatorInterface;
use Module\ApiPlatformEasyFilter\Filter\Operator\Adapter\StringOperatorInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * @see \App\Tests\ApiPlatform\Filter\Operator\IsNotNullOperatorTest
 */
class IsNotNullOperator implements OrmOperatorInterface, NumberOperatorInterface, StringOperatorInterface, ArrayOperatorInterface
{
    #[\Override]
    public function queryOperatorName(): string
    {
        return 'isnotnull';
    }

    #[\Override]
    public function description(): string
    {
        return 'is not null value';
    }

    #[\Override]
    public function apply(
        QueryBuilder                         $qb,
        string                               $rootAlias,
        string|array                         $value,
        \ReflectionProperty|FilterDefinition $filterDefinition,
    ): QueryBuilder
    {
        return $qb->andWhere(sprintf('%s.%s IS NOT NULL', $rootAlias, $filterDefinition->getName()));
    }
}
