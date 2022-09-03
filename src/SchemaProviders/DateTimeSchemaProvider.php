<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\Core\RegexUtils;
use Apie\DateformatToRegex\DateFormatToRegex;
use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Schema;
use DateTimeInterface;
use ReflectionClass;

/**
 * @implements SchemaProvider<TimeRelatedValueObjectInterface>
 */
class DateTimeSchemaProvider implements SchemaProvider
{
    public function supports(ReflectionClass $class): bool
    {
        return $class->implementsInterface(DateTimeInterface::class);
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
        $className = $class->name;
        $schema = new Schema([
            'type' => 'string',
            'format' => 'datetime',
            'pattern' => RegexUtils::removeDelimiters(
                DateFormatToRegex::formatToRegex(DateTimeInterface::ATOM)
            )
        ]);
        $componentsBuilder->setSchema($componentIdentifier, $schema);

        return $componentsBuilder->getComponents();
    }
}
