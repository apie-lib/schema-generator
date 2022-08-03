<?php
namespace Apie\SchemaGenerator\Interfaces;

use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use cebe\openapi\spec\Components;
use ReflectionClass;

/**
 * @template T of object
 */
interface SchemaProvider
{
    /**
     * @param ReflectionClass<object> $class
     */
    public function supports(ReflectionClass $class): bool;

    /**
     * @param ReflectionClass<T> $class
     */
    public function addDisplaySchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components;

    /**
     * @param ReflectionClass<T> $class
     */
    public function addCreationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components;
}
