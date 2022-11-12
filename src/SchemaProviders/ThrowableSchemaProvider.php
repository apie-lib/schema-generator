<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Schema;
use ReflectionClass;
use Throwable;

/**
 * Creates schemas for exceptions.
 * 
 * @implements SchemaProvider<Throwable>
 */
class ThrowableSchemaProvider implements SchemaProvider
{
    public function __construct(private bool $debug = false)
    {
    }

    public function supports(ReflectionClass $class): bool
    {
        return $class->name === Throwable::class || $class->implementsInterface(Throwable::class);
    }

    public function addDisplaySchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components {
        return $this->addCreationSchemaFor($componentsBuilder, $componentIdentifier, $class);
    }

    public function addCreationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components {
        $properties = [
            'message' => ['type' => 'string'],
        ];
        if ($this->debug) {
            $properties['trace'] = new Schema([
                'type' => 'object',
                'additionalProperties' => $componentsBuilder->getMixedReference()
            ]);
        }
        $schema = new Schema([
            'type' => 'object',
            'properties' => $properties,
            'required' => array_keys($properties),
        ]);
        $componentsBuilder->setSchema($componentIdentifier, $schema);

        return $componentsBuilder->getComponents();
    }
}
