<?php
namespace Apie\SchemaGenerator;

use Apie\TypeConverter\ReflectionTypeFactory;
use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;

final class SchemaGenerator
{
    public function __construct(
        private readonly ComponentsBuilderFactory $componentsBuilderFactory
    ) {
    }

    public function createSchema(string $typehint): Schema
    {
        $builder = $this->componentsBuilderFactory->createComponentsBuilder();
        $isArray = false;
        if (str_ends_with($typehint, '[]')) {
            $isArray = true;
            $typehint = substr($typehint, 0, strlen($typehint) - 2);
        }
        $schema = $builder->getSchemaForType(ReflectionTypeFactory::createReflectionType($typehint), $isArray);
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
