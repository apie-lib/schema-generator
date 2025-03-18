<?php
namespace Apie\SchemaGenerator\Interfaces;

use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use cebe\openapi\spec\Components;
use ReflectionClass;

/**
 * @template T of object
 * @extends SchemaProvider<T>
 */
interface ModifySchemaProvider extends SchemaProvider
{
    /**
     * @param ReflectionClass<object> $class
     */
    public function supports(ReflectionClass $class): bool;

    /**
     * @param ReflectionClass<T> $class
     */
    public function addModificationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components;
}
