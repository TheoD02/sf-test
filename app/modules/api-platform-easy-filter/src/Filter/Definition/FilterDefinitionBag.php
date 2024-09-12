<?php

declare(strict_types=1);

namespace Module\ApiPlatformEasyFilter\Filter\Definition;

class FilterDefinitionBag
{
    /**
     * @var array<FilterDefinition>
     */
    private readonly array $filterDefinitions;

    public function __construct(
        FilterDefinition ...$filterDefinitions
    )
    {
        $this->filterDefinitions = $filterDefinitions;
    }

    /**
     * @return array<FilterDefinition>
     */
    public function getFilterDefinitions(): array
    {
        return $this->filterDefinitions;
    }
}
