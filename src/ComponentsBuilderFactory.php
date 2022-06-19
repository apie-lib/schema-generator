<?php
namespace Apie\SchemaGenerator;

use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use Apie\SchemaGenerator\SchemaProviders\CompositeValueObjectSchemaProvider;
use Apie\SchemaGenerator\SchemaProviders\DateValueObjectSchemaProvider;
use Apie\SchemaGenerator\SchemaProviders\DtoSchemaProvider;
use Apie\SchemaGenerator\SchemaProviders\EnumSchemaProvider;
use Apie\SchemaGenerator\SchemaProviders\StringValueObjectSchemaProvider;

class ComponentsBuilderFactory
{
    /**
     * @var SchemaProvider[]
     */
    private array $schemaProviders;

    public function __construct(SchemaProvider... $schemaProviders)
    {
        $this->schemaProviders = $schemaProviders;
    }

    public static function createComponentsBuilderFactory(): self
    {
        return new self(
            new DtoSchemaProvider(),
            new CompositeValueObjectSchemaProvider(),
            new DateValueObjectSchemaProvider(),
            new StringValueObjectSchemaProvider(),
            new EnumSchemaProvider()
        );
    }

    public function createComponentsBuilder(): ComponentsBuilder
    {
        return new ComponentsBuilder(...$this->schemaProviders);
    }
}
