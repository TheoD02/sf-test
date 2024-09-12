<?php

declare(strict_types=1);

namespace Module\ApiPlatformEasyFilter\Filter\Trait;

trait ParameterNameGeneratorTrait
{
    public function generateParameterName(string $field, string $operator, int|string|null $suffix = null): string
    {
        if ($suffix === null) {
            return sprintf('%s_%s', $field, $operator);
        }

        return sprintf('%s_%s_%s', $field, $operator, $suffix);
    }
}
