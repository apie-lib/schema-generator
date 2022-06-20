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

class DateValueObjectSchemaProvider implements SchemaProvider
{
    public function supports(ReflectionClass $class): bool
    {
        return $class->implementsInterface(TimeRelatedValueObjectInterface::class);
    }
    public function addCreationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components {
        $className = $class->name;
        $schema = new Schema([
            'type' => 'string',
            'pattern' => RegexUtils::removeDelimiters(
                DateFormatToRegex::formatToRegex($className::getDateFormat())
            )
        ]);
        $componentsBuilder->setSchema($componentIdentifier, $schema);

        return $componentsBuilder->getComponents();
    }
}
