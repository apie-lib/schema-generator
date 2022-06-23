<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\Core\Entities\EntityInterface;
use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Schema;
use ReflectionClass;
use ReflectionMethod;

class EntitySchemaProvider implements SchemaProvider
{
    public function supports(ReflectionClass $class): bool
    {
        return $class->implementsInterface(EntityInterface::class);
    }
    public function addCreationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components {
        $properties = [];
        $required = [];
        $constructor = $class->getConstructor();
        if ($constructor) {
            $info = $componentsBuilder->getSchemaForMethod($constructor);
            $required = $info->required;
            $properties = $info->schemas;
        }
        foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (preg_match('/^(set)([A-Z].*)$/', $method->name)) {
                $info = $componentsBuilder->getSchemaForMethod($method);
                $lastItem = array_pop($info->schemas);
                $properties[lcfirst(substr($method->name, 3))] = $lastItem ? : $componentsBuilder->getMixedReference();
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
}
