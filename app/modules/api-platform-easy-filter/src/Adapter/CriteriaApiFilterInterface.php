<?php

declare(strict_types=1);

namespace Module\ApiPlatformEasyFilter\Adapter;

use Doctrine\Common\Collections\Criteria;

interface CriteriaApiFilterInterface extends ApiFilterInterface
{
    public function criteria(): Criteria;
}
