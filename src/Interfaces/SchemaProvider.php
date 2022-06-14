<?php
namespace Apie\SchemaGenerator\Interfaces;

use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use cebe\openapi\spec\Components;
use ReflectionClass;

interface SchemaProvider
{
    public function supports(ReflectionClass $class): bool;
    public function addCreationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components;
}
