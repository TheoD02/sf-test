<?php

namespace Module\ApiPlatformEasyFilter\Relation;

use Rekalogika\Mapper\Context\Context;
use Rekalogika\Mapper\Transformer\TransformerInterface;
use Rekalogika\Mapper\Transformer\TypeMapping;
use Rekalogika\Mapper\Util\TypeFactory;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\PropertyInfo\Type;

class Mapper implements TransformerInterface
{
    public function transform(mixed $source, mixed $target, ?Type $sourceType, ?Type $targetType, Context $context): mixed
    {
        dd($source, $target);
    }

    public function getSupportedTransformation(): iterable
    {
        yield new TypeMapping(
            TypeFactory::object(),
            TypeFactory::object(),
        );
    }
}
