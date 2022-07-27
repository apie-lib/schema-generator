<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\Core\Attributes\Optional;
use Apie\Core\Dto\DtoInterface;
use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Schema;
use ReflectionClass;
use ReflectionProperty;

class DtoSchemaProvider implements SchemaProvider
{
    public function supports(ReflectionClass $class): bool
    {
        return $class->implementsInterface(DtoInterface::class);
    }

    public function addDisplaySchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components {
        $required = [];
        $properties = [];
        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            /** @var ReflectionProperty $property */
            $propertyName = $property->getName();
            if (!$this->isOptional($property)) {
                $required[] = $propertyName;
            }
            $type = $property->getType();
            if (null === $type || 'mixed' === $type->getName()) {
                $properties[$propertyName] = $componentsBuilder->getMixedReference();
                continue;
            }
            $properties[$propertyName] = $componentsBuilder->addDisplaySchemaFor(
                $type->getName()
            );
            if ($properties[$propertyName] instanceof Schema) {
                $properties[$propertyName]->nullable = $type->allowsNull();
            }
        }
        $schema = new Schema([
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
        ]);
        $componentsBuilder->setSchema($componentIdentifier, $schema);
        return $componentsBuilder->getComponents();
    }

    public function addCreationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components {
        $required = [];
        $properties = [];
        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            /** @var ReflectionProperty $property */
            $propertyName = $property->getName();
            if (!$this->isOptional($property)) {
                $required[] = $propertyName;
            }
            $type = $property->getType();
            if (null === $type || 'mixed' === $type->getName()) {
                $properties[$propertyName] = $componentsBuilder->getMixedReference();
                continue;
            }
            $properties[$propertyName] = $componentsBuilder->addCreationSchemaFor(
                $type->getName()
            );
            if ($properties[$propertyName] instanceof Schema) {
                $properties[$propertyName]->nullable = $type->allowsNull();
            }
        }
        $schema = new Schema([
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
        ]);
        $componentsBuilder->setSchema($componentIdentifier, $schema);
        return $componentsBuilder->getComponents();
    }

    private function isOptional(ReflectionProperty $property)
    {
        // properties without a typehint always have default value null....
        if (!$property->hasType() && $property->getDefaultValue() !== null) {
            return true;
        }
        if ($property->hasType() && $property->hasDefaultValue()) {
            return true;
        }
        if (!empty($property->getAttributes(Optional::class))) {
            return true;
        }
        return false;
    }
}
