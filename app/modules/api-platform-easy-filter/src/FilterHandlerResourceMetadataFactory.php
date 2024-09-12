<?php

declare(strict_types=1);

namespace Module\ApiPlatformEasyFilter;

use ApiPlatform\Metadata\FilterInterface;
use ApiPlatform\Metadata\HeaderParameterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Operations;
use ApiPlatform\Metadata\Parameter;
use ApiPlatform\Metadata\Parameters;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use ApiPlatform\OpenApi\Model\Parameter as OpenApiParameter;
use ApiPlatform\Serializer\Filter\FilterInterface as SerializerFilterInterface;
use Module\ApiPlatformEasyFilter\Adapter\ApiFilterInterface;
use Module\ApiPlatformEasyFilter\Attribute\ApiParameter;
use Module\ApiPlatformEasyFilter\Attribute\AsApiFilter;
use Module\ApiPlatformEasyFilter\Filter\Definition\FilterDefinitionBag;
use Psr\Container\ContainerInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\DivisibleBy;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Unique;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FilterHandlerResourceMetadataFactory implements ResourceMetadataCollectionFactoryInterface
{
    /**
     * @var array<string, true>
     */
    private static array $alreadyProcessed = [];

    public function __construct(
        private readonly ResourceMetadataCollectionFactoryInterface $decorated,
        private readonly ?ContainerInterface                        $filterLocator = null,
    )
    {
    }

    #[\Override]
    public function create(string $resourceClass): ResourceMetadataCollection
    {
        /** @var class-string $resourceClass (here because Interface don't expose the type) */
        $resourceMetadataCollection = $this->decorated->create($resourceClass);

        foreach ($resourceMetadataCollection as $resource) {
            $operations = $resource->getOperations();

            if (! $operations instanceof Operations) {
                continue;
            }

            $internalPriority = -1;
            /**
             * @var string    $operationName
             * @var Operation $operation
             */
            foreach ($operations as $operationName => $operation) {
                if (\array_key_exists($operationName, self::$alreadyProcessed)) {
                    continue;
                }

                self::$alreadyProcessed[$operationName] = true;
                $filters = $operation->getFilters() ?? [];

                if ($filters === []) {
                    continue;
                }

                /** @var array<string, Parameter> $parameters */
                $parameters = [...$operation->getParameters() ?? []];
                foreach ($filters as $filter) {
                    if (class_exists($filter) && is_subclass_of($filter, ApiFilterInterface::class)) {
                        $reflectionClass = new \ReflectionClass($filter);
                        $attribute = $reflectionClass->getAttributes(AsApiFilter::class)[0] ?? null;

                        if ($attribute === null) {
                            continue;
                        }

                        /** @var FilterDefinitionBag $definition */
                        $definition = $reflectionClass->newInstance()->definition();
                        foreach ($definition->getFilterDefinitions() as $filterDefinition) {
                            $filterName = $filterDefinition->getField();
                            $filterOperators = $filterDefinition->getOperators();

                            foreach ($filterOperators as $filterOperator) {
                                $parameters[$filterName . $filterOperator->queryOperatorName()] = new QueryParameter(
                                    key: "{$filterName}[{$filterOperator->queryOperatorName()}]",
                                    schema: [
                                        'type' => 'string',
                                    ],
                                    description: "Filter {$filterName} by {$filterOperator->description()}",
                                    required: false,
                                );
                            }
                        }

                        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                            $propertyName = $reflectionProperty->getName();

                            $apiParameterAttribute = $reflectionProperty->getAttributes(ApiParameter::class)[0] ?? null;
                            $apiParameter = $apiParameterAttribute?->newInstance();

                            if ($apiParameter === null) {
                                throw new \LogicException(sprintf(
                                    'The property "%s" in the filter "%s" must have the #[ApiParameter] attribute.',
                                    $propertyName,
                                    $filter,
                                ));
                            }

                            if ($apiParameter->getKey() === null) {
                                $apiParameter = $apiParameter->withKey($reflectionProperty->getName());
                            }

                            $parameters[$apiParameter->getKey()] = $apiParameter;
                        }
                    }
                }

                foreach ($parameters as $key => $parameter) {
                    $parameter = $this->setDefaults($key, $parameter, $resourceClass);
                    $priority = $parameter->getPriority() ?? $internalPriority--;
                    $parameters[$key] = $parameter->withPriority($priority);
                }

                $operations->add($operationName, $operation->withParameters(new Parameters($parameters)));
            }
        }

        return $resourceMetadataCollection;
    }

    /**
     * @param class-string $resourceClass
     */
    private function setDefaults(string $key, Parameter $parameter, string $resourceClass): Parameter
    {
        if ($parameter->getKey() === null) {
            $parameter = $parameter->withKey($key);
        }

        $filter = $parameter->getFilter();
        if (\is_string($filter) && $this->filterLocator?->has($filter)) {
            $filter = $this->filterLocator->get($filter);
        }

        if ($filter instanceof SerializerFilterInterface && $parameter->getProvider() === null) {
            $parameter = $parameter->withProvider('api_platform.serializer.filter_parameter_provider');
        }

        // Read filter description to populate the Parameter
        $description = $filter instanceof FilterInterface ? $filter->getDescription($resourceClass) : [];
        /** @var array{type?: string} $schema */
        $schema = $description[$key]['schema'] ?? [];
        if ($parameter->getSchema() === null && $schema !== []) {
            $parameter = $parameter->withSchema($schema);
        }

        $property = $description[$key]['property'] ?? null;
        if ($parameter->getProperty() === null && $property !== null) {
            $parameter = $parameter->withProperty($property);
        }

        $required = $description[$key]['required'] ?? null;
        if ($parameter->getRequired() === null && $required !== null) {
            $parameter = $parameter->withRequired($required);
        }

        $openApi = $description[$key]['openapi'] ?? null;
        if (! $parameter->getOpenApi() instanceof OpenApiParameter && $openApi !== null) {
            if ($openApi instanceof OpenApiParameter) {
                $parameter = $parameter->withOpenApi($openApi);
            } elseif (\is_array($openApi)) {
                /** @phpstan-ignore-next-line */
                $schema = $schema ?? $openapi['schema'] ?? [];
                $parameter = $parameter->withOpenApi(new OpenApiParameter(
                    name: $key,
                    in: $parameter instanceof HeaderParameterInterface ? 'header' : 'query',
                    description: $description[$key]['description'] ?? '',
                    required: (bool) ($description[$key]['required'] ?? $openApi['required'] ?? false),
                    deprecated: (bool) ($openApi['deprecated'] ?? false),
                    allowEmptyValue: (bool) ($openApi['allowEmptyValue'] ?? true),
                    schema: $schema,
                    style: \is_string($openApi['style'] ?? null) ? $openApi['style'] : null,
                    explode: (bool) ($openApi['explode'] ?? true),
                    allowReserved: (bool) ($openApi['allowReserved'] ?? false),
                    example: $openApi['example'] ?? null,
                    // @phpstan-ignore-next-line argument.type (Need some check here)
                    examples: isset($openApi['examples']) ? new \ArrayObject($openApi['examples']) : null,
                ));
            }
        }

        /** @var ?array{exclusiveMinimum?: int, exclusiveMaximum?: int, minimum?: int, maximum?: int, pattern?: string, maxLength?: int, minLength?: int, minItems?: int, maxItems?: int, multipleOf?: int, uniqueItems?: bool, enum?: list<mixed>} $schema */
        $schema = $parameter->getSchema() ?? null;
        if ($parameter->getOpenApi() !== null && \is_bool($parameter->getOpenApi()) === false) {
            /** @var array{exclusiveMinimum?: int, exclusiveMaximum?: int, minimum?: int, maximum?: int, pattern?: string, maxLength?: int, minLength?: int, minItems?: int, maxItems?: int, multipleOf?: int, uniqueItems?: bool, enum?: list<mixed>} $schema */
            $schema = $parameter->getOpenApi()->getSchema();
        }

        // Only add validation if the Symfony Validator is installed
        if (interface_exists(ValidatorInterface::class) && ! $parameter->getConstraints()) {
            return $this->addSchemaValidation(
                parameter: $parameter,
                schema: $schema,
                required: $parameter->getRequired() ?? (\is_bool($required) && $required),
            );
        }

        return $parameter;
    }

    /**
     * @param array{exclusiveMinimum?: int, exclusiveMaximum?: int, minimum?: int, maximum?: int, pattern?: string, maxLength?: int, minLength?: int, minItems?: int, maxItems?: int, multipleOf?: int, uniqueItems?: bool, enum?: list<mixed>}|null $schema
     */
    private function addSchemaValidation(
        Parameter $parameter,
        ?array    $schema = null,
        bool      $required = false,
    ): Parameter
    {
        $assertions = [];

        if ($required) {
            $assertions[] = new NotNull(message: sprintf('The parameter "%s" is required.', $parameter->getKey()));
        }

        if (isset($schema['exclusiveMinimum'])) {
            $assertions[] = new GreaterThan(value: $schema['exclusiveMinimum']);
        }

        if (isset($schema['exclusiveMaximum'])) {
            $assertions[] = new LessThan(value: $schema['exclusiveMaximum']);
        }

        if (isset($schema['minimum'])) {
            $assertions[] = new GreaterThanOrEqual(value: $schema['minimum']);
        }

        if (isset($schema['maximum'])) {
            $assertions[] = new LessThanOrEqual(value: $schema['maximum']);
        }

        if (isset($schema['pattern'])) {
            $assertions[] = new Regex($schema['pattern']);
        }

        if (isset($schema['maxLength']) || isset($schema['minLength'])) {
            $assertions[] = new Length(min: $schema['minLength'] ?? null, max: $schema['maxLength'] ?? null);
        }

        if (isset($schema['minItems']) || isset($schema['maxItems'])) {
            $assertions[] = new Count(min: $schema['minItems'] ?? null, max: $schema['maxItems'] ?? null);
        }

        if (isset($schema['multipleOf'])) {
            $assertions[] = new DivisibleBy(value: $schema['multipleOf']);
        }

        if ($schema['uniqueItems'] ?? false) {
            $assertions[] = new Unique();
        }

        if (isset($schema['enum'])) {
            $assertions[] = new Choice(choices: $schema['enum']);
        }

        if ($assertions === []) {
            return $parameter;
        }

        if (\count($assertions) === 1) {
            return $parameter->withConstraints($assertions[0]);
        }

        return $parameter->withConstraints($assertions);
    }
}
