<?php


namespace Apie\SchemaGenerator\Contracts;

use Apie\OpenapiSchema\Contract\SchemaContract;
use Apie\SchemaGenerator\Context\SchemaContext;
use ReflectionClass;

interface SchemaProvider
{
    public function supports(ReflectionClass $class, ?SchemaContext $schemaContext = null): bool;

    public function toSchema(ReflectionClass $class, ?SchemaContext $schemaContext = null): SchemaContract;
}