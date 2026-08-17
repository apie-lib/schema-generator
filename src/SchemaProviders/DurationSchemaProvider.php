<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Schema;
use ReflectionClass;
use Time\Duration;

/**
 * @implements SchemaProvider<Duration>
 */
class DurationSchemaProvider implements SchemaProvider
{
    public const MAX_SECONDS_DURATION = 9223372035;

    public function supports(ReflectionClass $class): bool
    {
        return $class->name === Duration::class;
    }

    private function createSchema(): Schema
    {
        // json_encode Duration returns this structure: {"seconds":0,"nanoseconds":6000000,"negative":false}
        return new Schema([
            'type' => 'object',
            'description' => 'Represents a duration in seconds',
            'required' => ['seconds', 'nanoseconds', 'negative'],
            'properties' => [
                'seconds' => new Schema([
                    'type' => 'integer',
                    'description' => 'The number of seconds in the duration',
                    'minimum' => 0,
                    'maximum' => min(PHP_INT_MAX, self::MAX_SECONDS_DURATION),
                ]),
                'nanoseconds' => new Schema([
                    'type' => 'integer',
                    'description' => 'The number of nanoseconds in the duration',
                    'minimum' => 0,
                    'maximum' => 999999999
                ]),
                'negative' => new Schema([
                    'type' => 'boolean',
                    'description' => 'Whether the duration is negative',
                ]),
            ],
            'example' => [
                'seconds' => 0,
                'nanoseconds' => 6000000,
                'negative' => false,
            ],
        ]);
    }

    public function addDisplaySchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class,
        bool $nullable = false
    ): Components {
        $schema = $this->createSchema();
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
        $displaySchema = $this->createSchema();
        if ($nullable) {
            $displaySchema->nullable = true;
        }
        $componentsBuilder->setSchema($componentIdentifier, new Schema([
            'oneOf' => [
                $displaySchema,
                new Schema([
                    'type' => 'number',
                    'format' => 'integer',
                    'description' => 'A duration in milliseconds',
                    'example' => 3600000,
                    'minimum' => 0,
                    'maximum' => min(PHP_INT_MAX, self::MAX_SECONDS_DURATION * 1000 + 999),
                ]),
            ],
        ]));
        return $componentsBuilder->getComponents();
    }
}
