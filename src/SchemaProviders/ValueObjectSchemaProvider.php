<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\Core\RegexUtils;
use Apie\Core\ValueObjects\Interfaces\HasRegexValueObjectInterface;
use Apie\Core\ValueObjects\Interfaces\ValueObjectInterface;
use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Schema;
use ReflectionClass;

/**
 * Gets schema data from the toNative() return type hint.
 */
class ValueObjectSchemaProvider implements SchemaProvider
{
    public function supports(ReflectionClass $class): bool
    {
        return $class->implementsInterface(ValueObjectInterface::class);
    }

    public function addDisplaySchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components
    {
        return $this->getSchema($componentsBuilder, $componentIdentifier, $class, true);
        return $this->addCreationSchemaFor($componentsBuilder, $componentIdentifier, $class);
    }

    public function addCreationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components {
        return $this->getSchema($componentsBuilder, $componentIdentifier, $class, false);
    }

    private function getSchema(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class,
        bool $display
    ): Components {
        $type = $class->getMethod('toNative')->getReturnType();
        $schema = $componentsBuilder->getSchemaForType($type, false, $display);

        if ($class->implementsInterface(HasRegexValueObjectInterface::class)) {
            $className = $class->name;
            $schema->pattern = RegexUtils::removeDelimiters($className::getRegularExpression());
        }
        $componentsBuilder->setSchema($componentIdentifier, $schema);

        return $componentsBuilder->getComponents();
    }
}
