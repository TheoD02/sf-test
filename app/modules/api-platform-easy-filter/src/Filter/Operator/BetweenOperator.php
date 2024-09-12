<?php

declare(strict_types=1);

namespace Module\ApiPlatformEasyFilter\Filter\Operator;

use Module\ApiPlatformEasyFilter\Filter\Definition\FilterDefinition;
use Module\ApiPlatformEasyFilter\Filter\Operator\Adapter\NumberOperatorInterface;
use Module\ApiPlatformEasyFilter\Filter\Operator\Adapter\OrmOperatorInterface;
use Module\ApiPlatformEasyFilter\Filter\Trait\ParameterNameGeneratorTrait;
use Doctrine\ORM\QueryBuilder;
use Webmozart\Assert\Assert;

/**
 * @see \App\Tests\ApiPlatform\Filter\Operator\BetweenOperatorTest
 */
class BetweenOperator implements OrmOperatorInterface, NumberOperatorInterface
{
    use ParameterNameGeneratorTrait;

    #[\Override]
    public function description(): string
    {
        return 'between two values';
    }

    #[\Override]
    public function apply(
        QueryBuilder                         $qb,
        string                               $rootAlias,
        string|array                         $value,
        \ReflectionProperty|FilterDefinition $filterDefinition,
    ): QueryBuilder
    {
        Assert::isArray($value);

        $parameterName1 = $this->generateParameterName($filterDefinition->getName(), $this->queryOperatorName(), 1);
        $parameterName2 = $this->generateParameterName($filterDefinition->getName(), $this->queryOperatorName(), 2);

        return $qb->andWhere(
            sprintf(
                '%s.%s BETWEEN :%s AND :%s',
                $rootAlias,
                $filterDefinition->getName(),
                $parameterName1,
                $parameterName2,
            ),
        )
            ->setParameter($parameterName1, $value[0])
            ->setParameter($parameterName2, $value[1]);
    }

    #[\Override]
    public function queryOperatorName(): string
    {
        return 'between';
    }
}
