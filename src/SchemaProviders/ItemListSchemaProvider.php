<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\Core\Lists\ItemList;
use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
use ReflectionClass;

/**
 * Creates schemas for Item Lists.
 *
 * @implements SchemaProvider<ItemList>
 */
class ItemListSchemaProvider implements SchemaProvider
{
    public function supports(ReflectionClass $class): bool
    {
        return $class->isSubclassOf(ItemList::class) || $class->name === ItemList::class;
    }

    public function addDisplaySchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class,
        bool $nullable = false
    ): Components {
        return $this->addCreationSchemaFor($componentsBuilder, $componentIdentifier, $class, $nullable);
    }

    public function addCreationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class,
        bool $nullable = false
    ): Components {
        $type = $class->getMethod('offsetGet')->getReturnType();
        $schema = $componentsBuilder->getSchemaForType($type, true, nullable: $nullable);
        $componentsBuilder->setSchema($componentIdentifier, $schema);

        return $componentsBuilder->getComponents();
    }
}
