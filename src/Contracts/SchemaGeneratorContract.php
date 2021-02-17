<?php


namespace Apie\SchemaGenerator\Contracts;


use Apie\OpenapiSchema\Contract\SchemaContract;
use Apie\SchemaGenerator\Context\SchemaContext;
use ReflectionClass;
use Symfony\Component\PropertyInfo\Type;

interface SchemaGeneratorContract
{
    /**
     * @param Type[] $types
     * @param SchemaContext|null $schemaContext
     * @return SchemaContract
     */
    public function fromTypesToSchema(array $types, ?SchemaContext $schemaContext = null): SchemaContract;
    public function fromClassToSchema(ReflectionClass $class, ?SchemaContext $schemaContext = null): SchemaContract;
}