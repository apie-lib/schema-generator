<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\Core\Context\ApieContext;
use Apie\Core\Enums\ScalarType;
use Apie\Core\Metadata\EnumMetadata;
use Apie\Core\Metadata\MetadataFactory;
use Apie\Core\Metadata\MetadataInterface;
use Apie\Core\Metadata\ScalarMetadata;
use Apie\Core\Metadata\UnionTypeMetadata;
use Apie\Core\Metadata\ValueObjectMetadata;
use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use ReflectionClass;

/**
 * @implements SchemaProvider<object|string|int|float|bool|null>
 */
class MetadataSchemaProvider implements SchemaProvider
{
    private array $mapping = [
        EnumMetadata::class => 'createFromEnum',
        ScalarMetadata::class => 'createFromScalar',
        UnionTypeMetadata::class => 'createFromUnionType',
    ];

    public function supports(ReflectionClass $class): bool
    {
        return true;
    }

    private function createFromUnionType(ComponentsBuilder $componentsBuilder, UnionTypeMetadata $metadata, bool $display): Schema
    {
        $oneOfs = [];
        foreach ($metadata->getTypes() as $type) {
            $oneOfs[] = $this->createSchemaForMetadata($componentsBuilder, $metadata, $display);
        }
        return new Schema([
            'oneOf' => $oneOfs,
        ]);
    }

    private function createFromScalar(ComponentsBuilder $componentsBuilder, ScalarMetadata $metadata, bool $display): Schema
    {
        return match($metadata->toScalarType()) {
            ScalarType::NULL => new Schema(['nullable' => true]),
            ScalarType::ARRAY => new Schema(['type' => 'array', 'items' => $componentsBuilder->getMixedReference()]),
            ScalarType::STDCLASS => new Schema(['type' => 'object', 'additionalProperties' => $componentsBuilder->getMixedReference()]),
            default => new Schema([
                'type' => $metadata->toScalarType()->toJsonSchemaType(),
            ]),
        };
    }

    private function createFromEnum(ComponentsBuilder $componentsBuilder, EnumMetadata $metadata, bool $display): Schema
    {
        return new Schema([
            'type' => $metadata->toScalarType()->toJsonSchemaType(),
            'enum' => $metadata->getOptions(new ApieContext()),
        ]);
    }

    private function createSchemaForMetadata(ComponentsBuilder $componentsBuilder, MetadataInterface $metadata, bool $display): Schema|Reference
    {
        $className = get_class($metadata);
        if (isset($this->mapping[$className])) {
            return $this->{$this->mapping[$className]}($componentsBuilder, $metadata, $display);
        }

        $schema = new Schema(['type' => 'object']);
        $properties = [];
        foreach ($metadata->getHashmap() as $fieldName => $field) {
            if (!$field->isField()) {
                continue;
            }
            $type = $field->getTypehint();
            $properties[$fieldName] = $type ? $componentsBuilder->getSchemaForType($type, false, $display) : $componentsBuilder->getMixedReference();
            if ($properties[$fieldName] instanceof Schema) {
                $properties[$fieldName]->nullable = $field->allowsNull();
            }
        }
        $schema->required = $metadata->getRequiredFields()->toArray();
        $schema->properties = $properties;
        return $schema;
    }

    public function addDisplaySchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components
    {
        $componentsBuilder->setSchema(
            $componentIdentifier,
            $this->createSchemaForMetadata(
                $componentsBuilder,
                MetadataFactory::getResultMetadata($class, new ApieContext()),
                true
            )
        );
        return $componentsBuilder->getComponents();
    }

    public function addCreationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components
    {
        $componentsBuilder->setSchema(
            $componentIdentifier,
            $this->createSchemaForMetadata(
                $componentsBuilder,
                MetadataFactory::getCreationMetadata($class, new ApieContext()),
                false
            )
        );
        return $componentsBuilder->getComponents();
    }
}