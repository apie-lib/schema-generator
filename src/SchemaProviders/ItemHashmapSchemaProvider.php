<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\Core\Lists\ItemHashmap;
use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Schema;
use ReflectionClass;

/**
 * @implements SchemaProvider<ItemHashmap>
 */
class ItemHashmapSchemaProvider implements SchemaProvider
{
    public function supports(ReflectionClass $class): bool
    {
        return $class->isSubclassOf(ItemHashmap::class) || $class->name === ItemHashmap::class;
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
        $schema = $componentsBuilder->getSchemaForType($type, $nullable);
        $schema = new Schema([
            'type' => 'object',
            'additionalProperties' => $schema
        ]);
        if ($nullable) {
            $schema->nullable = true;
        }

        $componentsBuilder->setSchema($componentIdentifier, $schema);

        return $componentsBuilder->getComponents();
    }
}
