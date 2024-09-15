<?php

namespace App\User\Infrastructure\ApiPlatform\Filter;

use Doctrine\ORM\QueryBuilder;
use Module\ApiPlatformEasyFilter\Adapter\ApiFilterInterface;
use Module\ApiPlatformEasyFilter\Adapter\QueryBuilderApiFilterInterface;
use Module\ApiPlatformEasyFilter\Attribute\AsApiFilter;
use Module\ApiPlatformEasyFilter\Filter\Definition\FilterDefinition;
use Module\ApiPlatformEasyFilter\Filter\Definition\FilterDefinitionBag;
use Module\ApiPlatformEasyFilter\Filter\Operator\ContainsOperator;
use Module\ApiPlatformEasyFilter\Filter\Operator\StartsWithOperator;

#[AsApiFilter]
class UserCollectionFilter implements ApiFilterInterface, QueryBuilderApiFilterInterface
{

    public function definition(): FilterDefinitionBag
    {
        return new FilterDefinitionBag(FilterDefinition::create('email')->addStringOperators());
    }

    public function applyToQueryBuilder(QueryBuilder $qb): QueryBuilder
    {
        return $qb;
    }
}
