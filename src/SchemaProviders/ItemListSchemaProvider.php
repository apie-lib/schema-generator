<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\Core\Lists\ItemList;
use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
use ReflectionClass;

class ItemListSchemaProvider implements SchemaProvider
{
    public function supports(ReflectionClass $class): bool
    {
        return $class->isSubclassOf(ItemList::class) || $class->name === ItemList::class;
    }
    public function addCreationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components {
        $type = $class->getMethod('offsetGet')->getReturnType();
        $schema = $componentsBuilder->getSchemaForType($type, true);
        $componentsBuilder->setSchema($componentIdentifier, $schema);

        return $componentsBuilder->getComponents();
    }
}
