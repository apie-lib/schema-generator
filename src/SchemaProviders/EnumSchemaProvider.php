<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Schema;
use ReflectionClass;
use UnitEnum;

class EnumSchemaProvider implements SchemaProvider
{
    public function supports(ReflectionClass $class): bool
    {
        return $class->implementsInterface(UnitEnum::class);
    }
    public function addCreationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components {
        $className = $class->name;
        $cases = $className::cases();
        $firstCase = reset($cases);
        $schema = new Schema(
            [
                'type' => 'string',
                'enum' => array_keys($cases),
            ]
        );
        if ($firstCase) {
            $firstCase = (array) $firstCase;
            if (isset($firstCase['value'])) {
                if (gettype($firstCase['value']) === 'integer') {
                    $schema->type = 'integer';
                }
                $schema->enum = array_column(
                    array_map(function ($case) {
                        return (array) $case;
                    }, $cases),
                    'value'
                );
            }
        }
        $componentsBuilder->setSchema($componentIdentifier, $schema);
        return $componentsBuilder->getComponents();
    }
}
