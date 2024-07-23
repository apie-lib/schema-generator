<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\Core\ValueObjects\JsonFileUpload;
use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;

/**
 * @implements SchemaProvider<UploadedFileInterface>
 */
class UploadedFileSchemaProvider implements SchemaProvider
{
    public function supports(ReflectionClass $class): bool
    {
        return $class->name === UploadedFileInterface::class || in_array(UploadedFileInterface::class, $class->getInterfaceNames());
    }

    public function addDisplaySchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class,
        bool $nullable = false
    ): Components {
        $schema = new Schema(['type' => 'string', 'format' => 'path']);
        if ($nullable) {
            $schema->nullable = true;
        }
        $componentsBuilder->setSchema($componentIdentifier, $schema);

        return $componentsBuilder->getComponents();
    }

    public function addCreationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class,
        bool $nullable = false
    ): Components {
        if ($componentsBuilder->getContentType() && str_starts_with($componentsBuilder->getContentType(), 'multipart/')) {
            $schema = new Schema([
                'type' => 'string',
                'format' => 'binary',
                'x-upload' => '*/*',
                'nullable' => $nullable,
            ]);
        } else {
            $schema = $componentsBuilder->addCreationSchemaFor(JsonFileUpload::class, nullable: $nullable);
            if ($schema instanceof Reference) {
                $schema = $componentsBuilder->getSchemaForReference($schema);
            }
        }
        $componentsBuilder->setSchema($componentIdentifier, $schema);

        return $componentsBuilder->getComponents();
    }
}
