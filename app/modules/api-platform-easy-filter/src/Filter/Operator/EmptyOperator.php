<?php

declare(strict_types=1);

namespace Module\ApiPlatformEasyFilter\Filter\Operator;

use Module\ApiPlatformEasyFilter\Filter\Definition\FilterDefinition;
use Module\ApiPlatformEasyFilter\Filter\Operator\Adapter\ArrayOperatorInterface;
use Module\ApiPlatformEasyFilter\Filter\Operator\Adapter\OrmOperatorInterface;
use Module\ApiPlatformEasyFilter\Filter\Operator\Adapter\StringOperatorInterface;
use Module\ApiPlatformEasyFilter\Filter\Trait\ParameterNameGeneratorTrait;
use Doctrine\ORM\QueryBuilder;
use Webmozart\Assert\Assert;

/**
 * @see \App\Tests\ApiPlatform\Filter\Operator\EmptyOperatorTest
 */
class EmptyOperator implements OrmOperatorInterface, StringOperatorInterface, ArrayOperatorInterface
{
    use ParameterNameGeneratorTrait;

    #[\Override]
    public function queryOperatorName(): string
    {
        return 'empty';
    }

    #[\Override]
    public function description(): string
    {
        return 'empty or null value';
    }

    #[\Override]
    public function apply(
        QueryBuilder                         $qb,
        string                               $rootAlias,
        string|array                         $value,
        \ReflectionProperty|FilterDefinition $filterDefinition,
    ): QueryBuilder
    {
        Assert::string($value);

        return $qb->andWhere(
            sprintf(
                "%s.%s = '' OR %s.%s IS NULL",
                $rootAlias,
                $filterDefinition->getName(),
                $rootAlias,
                $filterDefinition->getName(),
            ),
        );
    }
}
