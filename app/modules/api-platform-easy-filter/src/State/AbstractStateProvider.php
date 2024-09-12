<?php

namespace Module\ApiPlatformEasyFilter\State;

use ApiPlatform\Metadata\Operation;
use Module\ApiPlatformEasyFilter\Filter\Applier\FilterApplierHandler;
use Rekalogika\ApiLite\Paginator\MappingPaginatorDecorator;
use Rekalogika\ApiLite\State\AbstractProvider;
use Rekalogika\Mapper\Context\Context;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Service\Attribute\Required;
use Webmozart\Assert\Assert;

/**
 * @template T of object
 */
abstract class AbstractStateProvider extends AbstractProvider
{
    private FilterApplierHandler $filterApplierHandler;

    #[Required]
    public function setFilterApplierHandler(FilterApplierHandler $filterApplierHandler): void
    {
        $this->filterApplierHandler = $filterApplierHandler;
    }

    /**
     * @return MappingPaginatorDecorator<T>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): MappingPaginatorDecorator
    {
        if ($this instanceof QueryBuilderStateProviderInterface) {
            $qb = $this->getQueryBuilderInstance();

            $this->provideWithQueryBuilder($operation, $uriVariables, $context);
        }

        if (! isset($qb)) {
            throw new \RuntimeException('QueryBuilder not set');
        }

        $this->filterApplierHandler->apply($qb, $operation);

        if ($this->isCollection()) {
            /** @var MappingPaginatorDecorator<T> */
            return $this->mapCollection(
                collection: $qb->getQuery(),
                target: $this->getTarget(),
                operation: $operation,
                context: $context,
                mapperContext: $this->getMapperContext(),
            );
        }

        return $this->map(
            source: $qb->getQuery()->getOneOrNullResult(),
            target: $this->getTarget(),
            context: $this->getMapperContext(),
        );
    }

    abstract public function isCollection(): bool;

    abstract public function getTarget(): string;

    public function getMapperContext(): ?Context
    {
        return null;
    }
}
