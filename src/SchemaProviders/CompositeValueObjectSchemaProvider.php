<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\CompositeValueObjects\CompositeValueObject;
use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Schema;
use ReflectionClass;

class CompositeValueObjectSchemaProvider implements SchemaProvider
{
    public function supports(ReflectionClass $class): bool
    {
        return in_array(CompositeValueObject::class, $class->getTraitNames());
    }

    public function addDisplaySchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components
    {
        $properties = [];
        $required = [];
        $className = $class->name;
        foreach ($className::getFields() as $fieldName => $field) {
            if (!$field->isOptional()) {
                $required[] = $fieldName;
            }
            $properties[$fieldName] = $componentsBuilder->addDisplaySchemaFor($field->getTypehint());
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
        $properties = [];
        $required = [];
        $className = $class->name;
        foreach ($className::getFields() as $fieldName => $field) {
            if (!$field->isOptional()) {
                $required[] = $fieldName;
            }
            $properties[$fieldName] = $componentsBuilder->addCreationSchemaFor($field->getTypehint());
        }

        $schema = new Schema([
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
        ]);
        $componentsBuilder->setSchema($componentIdentifier, $schema);
        return $componentsBuilder->getComponents();
    }
}
