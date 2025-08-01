<?php
namespace Apie\SchemaGenerator;

use Apie\TypeConverter\ReflectionTypeFactory;
use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use ReflectionMethod;

final class SchemaGenerator
{
    public function __construct(
        private readonly ComponentsBuilderFactory $componentsBuilderFactory
    ) {
    }

    public function createMethodSchema(ReflectionMethod $method): Schema
    {
        $builder = $this->componentsBuilderFactory->createComponentsBuilder();
        $methodSchema = $builder->getSchemaForMethod($method);
        $schema = new Schema([
            'type' => 'object',
            'properties' => $methodSchema->schemas,
            'required' => $methodSchema->required
        ]);
        $schema->resolveReferences(new ReferenceContext(
            new OpenApi(['components' => $builder->getComponents()]),
            'file:///#/components'
        ));
        return $schema;
    }

    public function createSchema(string $typehint, bool $display = false): Schema
    {
        $builder = $this->componentsBuilderFactory->createComponentsBuilder();
        $isArray = false;
        if (str_ends_with($typehint, '[]')) {
            $isArray = true;
            $typehint = substr($typehint, 0, strlen($typehint) - 2);
        }
        $schema = $builder->getSchemaForType(ReflectionTypeFactory::createReflectionType($typehint), $isArray, $display);
        if ($schema instanceof Reference) {
            $schema = $builder->getSchemaForReference($schema);
        }
        $schema->resolveReferences(new ReferenceContext(
            new OpenApi(['components' => $builder->getComponents()]),
            'file:///#/components'
        ));
        return $schema;
    }
}
