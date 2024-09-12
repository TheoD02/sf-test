<?php

declare(strict_types=1);

namespace Module\ApiPlatformEasyFilter\Filter\Applier;

use Module\ApiPlatformEasyFilterAdapter\ApiFilterInterface;
use Module\ApiPlatformEasyFilter\Filter\Definition\FilterDefinition;
use Module\ApiPlatformEasyFilter\Filter\Operator\Adapter\OperatorInterface;
use Module\ApiPlatformEasyFilter\Filter\Operator\Adapter\OrmOperatorInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * @template T of ApiFilterInterface
 */
class OrmFilterApplier
{
    /**
     * @param \ReflectionClass<T>                                                                                                                                 $reflectionClass
     * @param array<string, array{operator: OperatorInterface, queryFieldName: string, queryFieldValue: string, definition: FilterDefinition, fieldName: string}> $parameters
     */
    public function apply(QueryBuilder $qb, \ReflectionClass $reflectionClass, array $parameters = []): void
    {
        $rootAlias = $qb->getRootAliases()[0];

        foreach ($parameters as $parameter) {
            $fieldName = $parameter['fieldName'];
            /** @var OperatorInterface&OrmOperatorInterface $operator */
            $operator = $parameter['operator'];
            $queryFieldValue = $parameter['queryFieldValue'];
            $definition = $parameter['definition'];

            $reflectionProperty = $reflectionClass->hasProperty($fieldName) ? $reflectionClass->getProperty(
                $fieldName,
            ) : $definition;
            $operator->apply($qb, $rootAlias, $queryFieldValue, $reflectionProperty);
        }
    }
}
