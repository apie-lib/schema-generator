<?php

namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\Core\ApieLib;
use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\ModifySchemaProvider;
use Apie\TypeConverter\ReflectionTypeFactory;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Schema;
use ReflectionClass;

/**
 * Get OpenAPI schema from the apie/core MetadataFactory class.
 *
 * @implements ModifySchemaProvider<object>
 */
class AliasSchemaProvider implements ModifySchemaProvider
{
    public function supports(ReflectionClass $class): bool
    {
        return ApieLib::hasAlias($class->name);
    }

    public function addDisplaySchemaFor(ComponentsBuilder $componentsBuilder, string $componentIdentifier, ReflectionClass $class, bool $nullable = false): Components
    {
        $alias = ApieLib::getAlias($class->name);
        $type = ReflectionTypeFactory::createReflectionType($alias);
        $types = $type instanceof \ReflectionUnionType ? $type->getTypes() : [$type];
        $oneOfs = [];
        foreach ($types as $type) {
            $oneOfs[] = $componentsBuilder->getSchemaForType($type, false, true, $nullable);
        }
        $schema = new Schema([
            'oneOf' => $oneOfs
        ]);

        $componentsBuilder->setSchema($componentIdentifier, $schema);

        return $componentsBuilder->getComponents();
    }

    public function addCreationSchemaFor(ComponentsBuilder $componentsBuilder, string $componentIdentifier, ReflectionClass $class, bool $nullable = false): Components
    {
        $alias = ApieLib::getAlias($class->name);
        $type = ReflectionTypeFactory::createReflectionType($alias);
        $types = $type instanceof \ReflectionUnionType ? $type->getTypes() : [$type];
        $oneOfs = [];
        foreach ($types as $type) {
            $oneOfs[] = $componentsBuilder->getSchemaForType($type, false, false, $nullable);
        }
        $schema =  new Schema([
            'oneOf' => $oneOfs
        ]);

        $componentsBuilder->setSchema($componentIdentifier, $schema);

        return $componentsBuilder->getComponents();
    }

    public function addModificationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components {
        $alias = ApieLib::getAlias($class->name);
        $type = ReflectionTypeFactory::createReflectionType($alias);
        $types = $type instanceof \ReflectionUnionType ? $type->getTypes() : [$type];
        $oneOfs = [];
        foreach ($types as $type) {
            $oneOfs[] = $componentsBuilder->getSchemaForType($type, false, false, $type->allowsNull());
        }
        $schema =  new Schema([
            'oneOf' => $oneOfs
        ]);

        $componentsBuilder->setSchema($componentIdentifier, $schema);

        return $componentsBuilder->getComponents();
    }
}
