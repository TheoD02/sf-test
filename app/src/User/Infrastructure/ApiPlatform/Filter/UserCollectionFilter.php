<?php

declare(strict_types=1);

namespace App\User\Infrastructure\ApiPlatform\Filter;

use Doctrine\ORM\QueryBuilder;
use Module\ApiPlatformEasyFilter\Adapter\ApiFilterInterface;
use Module\ApiPlatformEasyFilter\Adapter\QueryBuilderApiFilterInterface;
use Module\ApiPlatformEasyFilter\Attribute\AsApiFilter;
use Module\ApiPlatformEasyFilter\Filter\Definition\FilterDefinition;
use Module\ApiPlatformEasyFilter\Filter\Definition\FilterDefinitionBag;

#[AsApiFilter]
class UserCollectionFilter implements ApiFilterInterface, QueryBuilderApiFilterInterface
{
    #[\Override]
    public function definition(): FilterDefinitionBag
    {
        return new FilterDefinitionBag(FilterDefinition::create('email')->addStringOperators());
    }

    #[\Override]
    public function applyToQueryBuilder(QueryBuilder $qb): QueryBuilder
    {
        return $qb;
    }
}
