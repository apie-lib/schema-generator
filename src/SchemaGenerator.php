<?php


namespace Apie\SchemaGenerator;

use Apie\OpenapiSchema\Contract\SchemaContract;
use Apie\SchemaGenerator\Context\SchemaContext;
use Apie\SchemaGenerator\Contracts\SchemaGeneratorContract;
use Apie\SchemaGenerator\Contracts\SchemaProvider;
use ReflectionClass;
use Symfony\Component\PropertyInfo\Type;

class SchemaGenerator implements SchemaGeneratorContract
{
    /**
     * @var SchemaProvider[]
     */
    private $schemaProviders;
    /**
     * @var SchemaProvider
     */
    private $defaultProvider;

    public function __construct(SchemaProvider $defaultProvider, SchemaProvider... $schemaProviders)
    {
        $this->defaultProvider = $defaultProvider;
        $this->schemaProviders = $schemaProviders;
    }

    public function fromClassToSchema(ReflectionClass $class, ?SchemaContext $schemaContext = null): SchemaContract
    {
        foreach ($this->schemaProviders as $schemaProvider) {
            if ($schemaProvider->supports($class, $schemaContext)) {
                return $schemaProvider->toSchema($class, $schemaContext);
            }
        }
        return $this->defaultProvider->toSchema($class, $schemaContext);
    }

    /**
     * @param Type[] $types
     * @param SchemaContext|null $schemaContext
     * @return SchemaContract
     */
    public function fromTypesToSchema(array $types, ?SchemaContext $schemaContext = null): SchemaContract
    {

    }
}