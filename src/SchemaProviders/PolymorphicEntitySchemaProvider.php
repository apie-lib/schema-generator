<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\Core\Entities\PolymorphicEntityInterface;
use Apie\Core\Other\DiscriminatorMapping;
use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Discriminator;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use ReflectionClass;

/**
 * @implements SchemaProvider<PolymorphicEntityInterface>
 */
class PolymorphicEntitySchemaProvider implements SchemaProvider
{
    public function supports(ReflectionClass $class): bool
    {
        if (!$class->implementsInterface(PolymorphicEntityInterface::class)) {
            return false;
        }
        $method = $class->getMethod('getDiscriminatorMapping');
        return $method->getDeclaringClass()->name === $class->name && !$method->isAbstract();
    }

    private function fillInDiscriminator(Schema $schema, string $propertyName, string $propertyValue): void
    {
        $properties = $schema->properties ?? [];
        $properties[$propertyName] = new Schema([
            'type' => 'string',
            'enum' => [$propertyValue],
            'nullable' => false,
        ]);
        $schema->properties = $properties;
    }

    public function addDisplaySchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class,
        bool $nullable = false
    ): Components {
        $relations = [];
        $method = $class->getMethod('getDiscriminatorMapping');
        /** @var DiscriminatorMapping */
        $discriminatorMapping = $method->invoke(null);

        foreach ($discriminatorMapping->getConfigs() as $config) {
            $key = $config->getDiscriminator();
            $value = $componentsBuilder->addDisplaySchemaFor($config->getClassName(), $discriminatorMapping->getPropertyName(), nullable: $nullable);
            assert($value instanceof Reference);
            $relations[$key] = $value;
            $schema = $componentsBuilder->getSchemaForReference($value);
            if ($schema) {
                $this->fillInDiscriminator($schema, $discriminatorMapping->getPropertyName(), $config->getDiscriminator());
            }
        }
        $schema = new Schema([
            'type' => 'object',
            'oneOf' => array_values($relations),
            'discriminator' => new Discriminator([
                'propertyName' => $discriminatorMapping->getPropertyName(),
                'mapping' => $relations,
            ]),
        ]);
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
        $relations = [];
        $method = $class->getMethod('getDiscriminatorMapping');
        /** @var DiscriminatorMapping */
        $discriminatorMapping = $method->invoke(null);
        foreach ($discriminatorMapping->getConfigs() as $config) {
            $key = $config->getDiscriminator();
            $value = $componentsBuilder->addCreationSchemaFor($config->getClassName(), $discriminatorMapping->getPropertyName());
            assert($value instanceof Reference);
            $relations[$key] = $value;
            $schema = $componentsBuilder->getSchemaForReference($value);
            if ($schema) {
                $this->fillInDiscriminator($schema, $discriminatorMapping->getPropertyName(), $config->getDiscriminator());
            }
        }
        $schema = new Schema([
            'type' => 'object',
            'oneOf' => array_values($relations),
            'discriminator' => new Discriminator([
                'propertyName' => $discriminatorMapping->getPropertyName(),
                'mapping' => $relations,
            ]),
        ]);
        if ($nullable) {
            $schema->nullable = true;
        }

        $componentsBuilder->setSchema($componentIdentifier, $schema);
        return $componentsBuilder->getComponents();
    }
}
