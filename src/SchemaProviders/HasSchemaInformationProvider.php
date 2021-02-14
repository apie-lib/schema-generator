<?php


namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\OpenapiSchema\Contract\SchemaContract;
use Apie\SchemaGenerator\Context\SchemaContext;
use Apie\Core\Interfaces\HasSchemaInformationContract;
use Apie\SchemaGenerator\Contracts\SchemaProvider;
use ReflectionClass;

class HasSchemaInformationProvider implements SchemaProvider
{
    public function supports(ReflectionClass $class, ?SchemaContext $schemaContext = null): bool
    {
        return $class->implementsInterface(HasSchemaInformationContract::class);
    }

    public function toSchema(ReflectionClass $class, ?SchemaContext $schemaContext = null): SchemaContract
    {
        return $class->getMethod('toSchema')->invoke(null);
    }
}