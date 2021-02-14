<?php


namespace Apie\SchemaGenerator;

use Apie\OpenapiSchema\Contract\SchemaContract;
use Apie\SchemaGenerator\Context\SchemaContext;
use Apie\SchemaGenerator\Contracts\SchemaGeneratorContract;
use Apie\SchemaGenerator\Contracts\SchemaProvider;
use ReflectionClass;

class SchemaGenerator implements SchemaGeneratorContract
{
    /**
     * @var SchemaProvider[]
     */
    private $schemaProviders;

    public function __construct(SchemaProvider... $schemaProviders)
    {
        $this->schemaProviders = $schemaProviders;
    }

    public function fromClassToSchema(ReflectionClass $class, ?SchemaContext $schemaContext = null): SchemaContract
    {
        foreach ($this->schemaProviders as $schemaProvider) {
            if ($schemaProvider->supports($class, $schemaContext)) {
                return $schemaProvider->toSchema($class, $schemaContext);
            }
        }
        // TODO exception
    }
}