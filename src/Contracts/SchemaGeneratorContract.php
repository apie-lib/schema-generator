<?php


namespace Apie\SchemaGenerator\Contracts;


use Apie\OpenapiSchema\Contract\SchemaContract;
use Apie\SchemaGenerator\Context\SchemaContext;
use ReflectionClass;

interface SchemaGeneratorContract
{
    public function fromClassToSchema(ReflectionClass $class, ?SchemaContext $schemaContext = null): SchemaContract;
}