<?php

namespace Module\ApiPlatformEasyFilter\State;

use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;

interface QueryBuilderStateProviderInterface
{
    public function getQueryBuilderInstance(): QueryBuilder;

    public function provideWithQueryBuilder(Operation $operation, array $uriVariables, array $context = []): QueryBuilder;
}
