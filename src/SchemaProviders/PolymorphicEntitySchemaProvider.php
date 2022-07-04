<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\Core\Entities\PolymorphicEntityInterface;
use Apie\Core\Other\DiscriminatorMapping;
use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Discriminator;
use cebe\openapi\spec\Schema;
use ReflectionClass;

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
    public function addCreationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class
    ): Components {
        $relations = [];
        $method = $class->getMethod('getDiscriminatorMapping');
        /** @var DiscriminatorMapping */
        $discriminatorMapping = $method->invoke(null);
        foreach ($discriminatorMapping->getConfigs() as $config) {
            $key = $config->getDiscriminator();
            $value = $componentsBuilder->addCreationSchemaFor($config->getClassName(), $discriminatorMapping->getPropertyName());
            $relations[$key] = $value;
        }
        $schema = new Schema([
            'oneOf' => array_values($relations),
            'discriminator' => new Discriminator([
                'propertyName' => $discriminatorMapping->getPropertyName(),
                'mapping' => $relations,
            ]),
        ]);

        $componentsBuilder->setSchema($componentIdentifier, $schema);
        return $componentsBuilder->getComponents();
    }
}
