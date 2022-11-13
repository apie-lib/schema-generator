<?php
namespace Apie\SchemaGenerator;

use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use Apie\SchemaGenerator\SchemaProviders\CompositeValueObjectSchemaProvider;
use Apie\SchemaGenerator\SchemaProviders\DateTimeSchemaProvider;
use Apie\SchemaGenerator\SchemaProviders\DateValueObjectSchemaProvider;
use Apie\SchemaGenerator\SchemaProviders\DtoSchemaProvider;
use Apie\SchemaGenerator\SchemaProviders\EntitySchemaProvider;
use Apie\SchemaGenerator\SchemaProviders\EnumSchemaProvider;
use Apie\SchemaGenerator\SchemaProviders\ItemHashmapSchemaProvider;
use Apie\SchemaGenerator\SchemaProviders\ItemListSchemaProvider;
use Apie\SchemaGenerator\SchemaProviders\MetadataSchemaProvider;
use Apie\SchemaGenerator\SchemaProviders\PolymorphicEntitySchemaProvider;
use Apie\SchemaGenerator\SchemaProviders\SchemaAttributeProvider;
use Apie\SchemaGenerator\SchemaProviders\StringValueObjectSchemaProvider;
use Apie\SchemaGenerator\SchemaProviders\ThrowableSchemaProvider;
use Apie\SchemaGenerator\SchemaProviders\ValueObjectSchemaProvider;

class ComponentsBuilderFactory
{
    /**
     * @var array<int, SchemaProvider<object>>
     */
    private array $schemaProviders;

    /**
     * @param SchemaProvider<object> $schemaProviders
     */
    public function __construct(SchemaProvider... $schemaProviders)
    {
        $this->schemaProviders = $schemaProviders;
    }

    public static function createComponentsBuilderFactory(bool $debug = false): self
    {
        return new self(
            new SchemaAttributeProvider(),
            new ThrowableSchemaProvider($debug),
            new ItemListSchemaProvider(),
            new ItemHashmapSchemaProvider(),
            new PolymorphicEntitySchemaProvider(),
            new DateTimeSchemaProvider(),
            new DateValueObjectSchemaProvider(),
            new StringValueObjectSchemaProvider(),
            new ValueObjectSchemaProvider(),
            new MetadataSchemaProvider(),
        );
    }

    public function createComponentsBuilder(): ComponentsBuilder
    {
        return new ComponentsBuilder(...$this->schemaProviders);
    }
}
