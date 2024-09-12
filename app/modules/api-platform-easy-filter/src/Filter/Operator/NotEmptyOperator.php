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
 * @see \App\Tests\ApiPlatform\Filter\Operator\NotEmptyOperatorTest
 */
class NotEmptyOperator implements OrmOperatorInterface, StringOperatorInterface, ArrayOperatorInterface
{
    use ParameterNameGeneratorTrait;

    #[\Override]
    public function description(): string
    {
        return 'not empty or null value';
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
                '%s.%s IS NOT NULL AND %s.%s != :%s',
                $rootAlias,
                $filterDefinition->getName(),
                $rootAlias,
                $filterDefinition->getName(),
                $this->generateParameterName($filterDefinition->getName(), $this->queryOperatorName()),
            ),
        )
            ->setParameter($this->generateParameterName($filterDefinition->getName(), $this->queryOperatorName()), '');
    }

    #[\Override]
    public function queryOperatorName(): string
    {
        return 'notempty';
    }
}
