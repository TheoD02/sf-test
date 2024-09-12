<?php

namespace Module\ApiPlatformEasyFilter\Trait;

use Module\ApiPlatformEasyFilter\Filter\Applier\FilterApplierHandler;
use Symfony\Contracts\Service\Attribute\Required;

trait FilterApplierTrait
{
    private FilterApplierHandler $filterApplierHandler;

    #[Required]
    public function setFilterApplierHandler(FilterApplierHandler $filterApplierHandler): void
    {
        $this->filterApplierHandler = $filterApplierHandler;
    }
}
