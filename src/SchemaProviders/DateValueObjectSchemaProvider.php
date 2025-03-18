<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\Core\RegexUtils;
use Apie\Core\ValueObjects\Interfaces\TimeRelatedValueObjectInterface;
use Apie\DateformatToRegex\DateFormatToRegex;
use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Schema;
use ReflectionClass;

/**
 * @implements SchemaProvider<TimeRelatedValueObjectInterface>
 */
class DateValueObjectSchemaProvider implements SchemaProvider
{
    public function supports(ReflectionClass $class): bool
    {
        return $class->implementsInterface(TimeRelatedValueObjectInterface::class);
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
        $className = $class->name;
        $schema = new Schema([
            'type' => 'string',
            'pattern' => RegexUtils::removeDelimiters(
                DateFormatToRegex::formatToRegex($className::getDateFormat())
            )
        ]);
        if ($nullable) {
            $schema->nullable = true;
        }
        $componentsBuilder->setSchema($componentIdentifier, $schema);

        return $componentsBuilder->getComponents();
    }
}
