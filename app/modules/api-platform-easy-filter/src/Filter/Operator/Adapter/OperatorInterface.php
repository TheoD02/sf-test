<?php

declare(strict_types=1);

namespace Module\ApiPlatformEasyFilter\Filter\Operator\Adapter;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface OperatorInterface
{
    /**
     * Returns the name of the operator.
     *
     * Like "eq", "neq". It will be used in the query string. (e.g. ?name[eq]=John)
     */
    public function queryOperatorName(): string;

    /**
     * Returns the description of the operator for the API documentation.
     *
     * You can use %fieldName% as a placeholder for the field name.
     */
    public function description(): string;
}
