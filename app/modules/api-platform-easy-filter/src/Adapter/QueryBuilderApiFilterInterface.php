<?php

declare(strict_types=1);

namespace Module\ApiPlatformEasyFilter\Adapter;

use Doctrine\ORM\QueryBuilder;

interface QueryBuilderApiFilterInterface extends ApiFilterInterface
{
    public function applyToQueryBuilder(QueryBuilder $qb): QueryBuilder;
}
