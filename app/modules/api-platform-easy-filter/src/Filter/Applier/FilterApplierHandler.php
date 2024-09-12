<?php

declare(strict_types=1);

namespace Module\ApiPlatformEasyFilter\Filter\Applier;

use ApiPlatform\Metadata\Operation;
use Module\ApiPlatformEasyFilter\Adapter\ApiFilterInterface;
use Module\ApiPlatformEasyFilter\Adapter\QueryBuilderApiFilterInterface;
use Module\ApiPlatformEasyFilter\Filter\Definition\FilterDefinition;
use Module\ApiPlatformEasyFilter\Filter\Definition\FilterDefinitionBag;
use Module\ApiPlatformEasyFilter\Filter\Operator\Adapter\OperatorInterface;
use Module\ApiPlatformEasyFilter\Filter\Operator\Adapter\OrmOperatorInterface;
use Module\ApiPlatformEasyFilter\Filter\Trait\SortTrait;
use Module\ApiPlatformEasyFilter\RequestToDTOTransformer;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @template T of ApiFilterInterface
 */
readonly class FilterApplierHandler
{
    /**
     * @param OrmFilterApplier<T> $ormFilterApplier
     */
    public function __construct(
        private RequestStack            $requestStack,
        private OrmFilterApplier        $ormFilterApplier,
        private RequestToDTOTransformer $requestToDTOTransformer,
    )
    {
    }

    public function apply(QueryBuilder $qb, Operation $operation): void
    {
        $filters = $operation->getFilters();

        if ($filters === null) {
            return;
        }

        foreach ($filters as $filter) {
            if (is_subclass_of($filter, ApiFilterInterface::class)) {
                $this->applyOrm($qb, $filter);

                if (is_subclass_of($filter, QueryBuilderApiFilterInterface::class) === false && \in_array(
                        SortTrait::class,
                        class_uses($filter) ?: [],
                        true,
                    )) {
                    throw new \LogicException(sprintf(
                        'Filter "%s" must implement "%s" to use sorting.',
                        $filter,
                        QueryBuilderApiFilterInterface::class,
                    ));
                }
            }
        }
    }

    /**
     * @param class-string $filterClass
     */
    public function applyOrm(QueryBuilder $qb, string $filterClass): void
    {
        /** @var \ReflectionClass<T> $reflectionClass */
        $reflectionClass = new \ReflectionClass($filterClass);

        $parameters = $this->parseQueryParameters($reflectionClass, for: ['orm']);

        if (is_subclass_of($filterClass, QueryBuilderApiFilterInterface::class)) {
            $request = $this->requestStack->getCurrentRequest();

            if (! $request instanceof Request) {
                throw new \RuntimeException('Request not found');
            }

            $filterClassInstance = $this->requestToDTOTransformer->transformQueryString($request, $filterClass);

            if ($filterClassInstance instanceof QueryBuilderApiFilterInterface) {
                $filterClassInstance->applyToQueryBuilder($qb);
            }
        }

        $this->ormFilterApplier->apply($qb, $reflectionClass, $parameters);
    }

    /**
     * @param \ReflectionClass<T> $reflectionClass
     * @param array<string>       $for
     *
     * @return array<string, array{operator: OperatorInterface, queryFieldName: string, queryFieldValue: string, definition: FilterDefinition, fieldName: string}>
     */
    private function parseQueryParameters(\ReflectionClass $reflectionClass, array $for = ['orm']): array
    {
        $queryParameters = $this->requestStack->getCurrentRequest()?->query->all();
        if ($queryParameters === null) {
            return [];
        }

        /** @var array<string, array{operator: OperatorInterface, queryFieldName: string, queryFieldValue: string, definition: FilterDefinition, fieldName: string}> $parameters */
        $parameters = [];

        /** @var FilterDefinitionBag $filterDefinitionBag */
        $filterDefinitionBag = $reflectionClass->newInstance()->definition();

        foreach ($filterDefinitionBag->getFilterDefinitions() as $filterDefinition) {
            foreach ($filterDefinition->getOperators() as $operator) {
                $queryFieldName = "{$filterDefinition->getField()}[{$operator->queryOperatorName()}]";
                /** @var ?string $queryFieldValue */
                $queryFieldValue = $queryParameters[$filterDefinition->getField()][$operator->queryOperatorName()] ?? null;

                if ($queryFieldValue === null) {
                    continue;
                }

                if ($operator instanceof OrmOperatorInterface && ! \in_array('orm', $for, true)) {
                    continue;
                }

                $parameters["{$filterDefinition->getField()}_{$operator->queryOperatorName()}"] = [
                    'operator' => $operator,
                    'queryFieldName' => $queryFieldName,
                    'queryFieldValue' => $queryFieldValue,
                    'definition' => $filterDefinition,
                    'fieldName' => $filterDefinition->getField(),
                ];
            }
        }

        return $parameters;
    }
}
