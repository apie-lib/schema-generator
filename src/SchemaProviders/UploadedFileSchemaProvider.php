<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
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
                'format' => 'filename',
                'x-upload' => '*/*',
                'nullable' => $nullable,
            ]);
        } else {
            $schema = new Schema([
                'type' => 'object',
                'properties' => [
                    'contents' => new Schema([
                        'type' => 'string',
                        'format' => 'binary',
                    ]),
                    'originalFilename' => new Schema([
                        'type' => 'string',
                        'format' => 'file',
                    ])
                ],
                'required' => ['contents', 'originalFilename'],
                'nullable' => $nullable,
            ]);
        }
        $componentsBuilder->setSchema($componentIdentifier, $schema);

        return $componentsBuilder->getComponents();
    }
}
