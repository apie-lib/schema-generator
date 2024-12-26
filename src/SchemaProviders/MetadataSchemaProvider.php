<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\Core\Context\ApieContext;
use Apie\Core\Enums\DoNotChangeUploadedFile;
use Apie\Core\Enums\ScalarType;
use Apie\Core\Metadata\EnumMetadata;
use Apie\Core\Metadata\Fields\PublicProperty;
use Apie\Core\Metadata\Fields\SetterMethod;
use Apie\Core\Metadata\MetadataFactory;
use Apie\Core\Metadata\MetadataInterface;
use Apie\Core\Metadata\ScalarMetadata;
use Apie\Core\Metadata\UnionTypeMetadata;
use Apie\Core\Utils\ConverterUtils;
use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\ModifySchemaProvider;
use Apie\TypeConverter\ReflectionTypeFactory;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

/**
 * Get OpenAPI schema from the apie/core MetadataFactory class.
 *
 * @implements ModifySchemaProvider<object>
 */
class MetadataSchemaProvider implements ModifySchemaProvider
{
    /**
     * @var array<class-string<MetadataInterface>, string> $mapping
     */
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
            $oneOfs[] = $this->createSchemaForMetadata($componentsBuilder, $metadata, $display, false);
        }
        return new Schema([
            'oneOf' => $oneOfs,
        ]);
    }

    private function createFromScalar(ComponentsBuilder $componentsBuilder, ScalarMetadata $metadata, bool $display): Schema
    {
        return match ($metadata->toScalarType()) {
            ScalarType::NULLVALUE => new Schema(['nullable' => true]),
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
            'enum' => array_values($metadata->getOptions(new ApieContext())),
        ]);
    }

    private function uploadedFileCheck(?ReflectionType $type): ?ReflectionType
    {
        if ($type === null) {
            return null;
        }
        if ($type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType) {
            return $type;
        }
        assert($type instanceof ReflectionNamedType);
        $class = ConverterUtils::toReflectionClass($type);
        if ($class !== null && ($class->name === UploadedFileInterface::class || in_array(UploadedFileInterface::class, $class->getInterfaceNames()))) {
            return ReflectionTypeFactory::createReflectionType(implode(
                '|',
                $type->allowsNull()
                    ? [$class->name, DoNotChangeUploadedFile::class, 'null']
                    : [$class->name, DoNotChangeUploadedFile::class]
            ));
        }
        return $type;
    }

    private function createSchemaForMetadata(ComponentsBuilder $componentsBuilder, MetadataInterface $metadata, bool $display, bool $nullable): Schema|Reference
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
            if (!$display && ($field instanceof PublicProperty || $field instanceof SetterMethod)) {
                $type = $this->uploadedFileCheck($type);
            }
            $properties[$fieldName] = $type ? $componentsBuilder->getSchemaForType($type, false, $display) : $componentsBuilder->getMixedReference();
            if ($properties[$fieldName] instanceof Schema) {
                $properties[$fieldName]->nullable = $field->allowsNull();
            }
        }
        $required = $metadata->getRequiredFields()->toArray();
        if (!empty($required)) {
            $schema->required = $required;
        }
        if ($nullable) {
            $schema->nullable = true;
        }
        $schema->properties = $properties;
        return $schema;
    }

    public function addDisplaySchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class,
        bool $nullable = false
    ): Components {
        $componentsBuilder->setSchema(
            $componentIdentifier,
            $this->createSchemaForMetadata(
                $componentsBuilder,
                MetadataFactory::getResultMetadata($class, new ApieContext()),
                true,
                $nullable
            )
        );
        return $componentsBuilder->getComponents();
    }

    public function addCreationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class,
        bool $nullable = false
    ): Components {
        $componentsBuilder->setSchema(
            $componentIdentifier,
            $this->createSchemaForMetadata(
                $componentsBuilder,
                MetadataFactory::getCreationMetadata($class, new ApieContext()),
                false,
                $nullable
            )
        );
        return $componentsBuilder->getComponents();
    }

    public function addModificationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components {
        $componentsBuilder->setSchema(
            $componentIdentifier,
            $this->createSchemaForMetadata(
                $componentsBuilder,
                MetadataFactory::getModificationMetadata($class, new ApieContext()),
                false,
                false
            )
        );
        return $componentsBuilder->getComponents();
    }
}
