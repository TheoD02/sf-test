<?php

declare(strict_types=1);

namespace Module\ApiPlatformEasyFilter\Filter\Operator;

use Module\ApiPlatformEasyFilter\Filter\Definition\FilterDefinition;
use Module\ApiPlatformEasyFilter\Filter\Operator\Adapter\OrmOperatorInterface;
use Module\ApiPlatformEasyFilter\Filter\Operator\Adapter\StringOperatorInterface;
use Module\ApiPlatformEasyFilter\Filter\Trait\ParameterNameGeneratorTrait;
use Doctrine\ORM\QueryBuilder;
use Webmozart\Assert\Assert;

/**
 * @see \App\Tests\ApiPlatform\Filter\Operator\NotEndsWithOperatorTest
 */
class NotEndsWithOperator implements OrmOperatorInterface, StringOperatorInterface
{
    use ParameterNameGeneratorTrait;

    #[\Override]
    public function description(): string
    {
        return 'not partial string match at the end';
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

        $parameterName = $this->generateParameterName($filterDefinition->getName(), $this->queryOperatorName());

        return $qb->andWhere(sprintf('%s.%s NOT LIKE :%s', $rootAlias, $filterDefinition->getName(), $parameterName))
            ->setParameter($parameterName, "%{$value}");
    }

    #[\Override]
    public function queryOperatorName(): string
    {
        return 'notendswith';
    }
}
