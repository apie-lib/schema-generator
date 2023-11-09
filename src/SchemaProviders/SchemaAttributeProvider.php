<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\Core\Attributes\SchemaMethod;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\Exceptions\MethodIsNotStaticException;
use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Enums\SchemaUsages;
use Apie\SchemaGenerator\Exceptions\ICanNotExtractASchemaFromClassException;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Schema;
use ReflectionClass;

/**
 * Reads #SchemaMethod attributes on classes.
 *
 * @implements SchemaProvider<object>
 */
class SchemaAttributeProvider implements SchemaProvider
{
    public function supports(ReflectionClass $class): bool
    {
        return !empty($class->getAttributes(SchemaMethod::class));
    }

    public function addDisplaySchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class,
        bool $nullable = false
    ): Components {
        return $this->getSchema($componentsBuilder, $componentIdentifier, $class, SchemaUsages::GET, $nullable);
    }
    
    public function addCreationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class,
        bool $nullable = false
    ): Components {
        return $this->getSchema($componentsBuilder, $componentIdentifier, $class, SchemaUsages::CREATE, $nullable);
    }

    /**
     * @param ReflectionClass<object> $class
     */
    private function getSchema(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class,
        SchemaUsages $usage,
        bool $nullable
    ): Components {
        foreach ($class->getAttributes(SchemaMethod::class) as $schemaMethod) {
            $method = $class->getMethod($schemaMethod->newInstance()->methodName);
            if (!$method->isStatic()) {
                throw new MethodIsNotStaticException($method);
            }
            $result = $method->invoke(null, $usage);
            if (is_array($result)) {
                $result = new Schema($result);
            }
            if ($result === null) {
                continue;
            }
            if (!$result instanceof Schema || !$result->validate()) {
                throw new InvalidTypeException($result, Schema::class);
            }
            if ($nullable) {
                $result->nullable = true;
            }
            $componentsBuilder->setSchema($componentIdentifier, $result);

            return $componentsBuilder->getComponents();
        }
        throw new ICanNotExtractASchemaFromClassException($class->name);
    }
}
