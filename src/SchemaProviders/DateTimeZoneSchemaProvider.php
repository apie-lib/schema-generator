<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Schema;
use DateTimeZone;
use ReflectionClass;

/**
 * @implements SchemaProvider<DateTimeZone>
 */
class DateTimeZoneSchemaProvider implements SchemaProvider
{
    public function supports(ReflectionClass $class): bool
    {
        return $class->name === DateTimeZone::class || $class->isSubclassOf(DateTimeZone::class);
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
        $schema = new Schema([
            'type' => 'string',
            'format' => 'datetimezone',
            'pattern' => implode(
                '|',
                array_map(
                    'preg_quote',
                    DateTimeZone::listIdentifiers()
                )
            ),
        ]);
        if ($nullable) {
            $schema->nullable = true;
        }
        $componentsBuilder->setSchema($componentIdentifier, $schema);

        return $componentsBuilder->getComponents();
    }
}
