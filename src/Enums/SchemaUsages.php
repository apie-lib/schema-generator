<?php
namespace Apie\SchemaGenerator\Enums;

use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;

enum SchemaUsages: string
{
    case CREATE = 'post';
    case MODIFY = 'patch';
    case GET = 'get';

    public function toSchema(ComponentsBuilder $componentsBuilder, string $className, bool $nullable = false): Schema|Reference
    {
        if ($this === self::MODIFY) {
            return $componentsBuilder->addModificationSchemaFor($className);
        }
        $method = match($this) {
            self::CREATE => 'addCreationSchemaFor',
            default => 'addDisplaySchemaFor'
        };
        return $componentsBuilder->$method($className, nullable: $nullable);
    }
}
