<?php

declare(strict_types=1);

namespace Module\ApiPlatformEasyFilter\Adapter;

use Module\ApiPlatformEasyFilter\Filter\Definition\FilterDefinitionBag;

interface ApiFilterInterface
{
    public function definition(): FilterDefinitionBag;
}
