<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\Core\RegexUtils;
use Apie\Core\ValueObjects\Interfaces\HasRegexValueObjectInterface;
use Apie\Core\ValueObjects\Interfaces\StringValueObjectInterface;
use Apie\Core\ValueObjects\Utils;
use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Schema;
use ReflectionClass;

class StringValueObjectSchemaProvider implements SchemaProvider
{
    public function supports(ReflectionClass $class): bool
    {
        return $class->implementsInterface(StringValueObjectInterface::class);
    }

    public function addDisplaySchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components
    {
        return $this->addCreationSchemaFor($componentsBuilder, $componentIdentifier, $class);
    }

    public function addCreationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components {
        $schema = new Schema([
            'type' => 'string',
            'format' => strtolower(Utils::getDisplayNameForValueObject($class))
        ]);
        if ($class->implementsInterface(HasRegexValueObjectInterface::class)) {
            $className = $class->name;
            $schema->pattern = RegexUtils::removeDelimiters($className::getRegularExpression());
        }
        $componentsBuilder->setSchema($componentIdentifier, $schema);

        return $componentsBuilder->getComponents();
    }
}
