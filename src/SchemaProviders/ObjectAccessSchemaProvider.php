<?php


namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\ObjectAccessNormalizer\ObjectAccess\FilteredObjectAccess;
use Apie\OpenapiSchema\Contract\SchemaContract;
use Apie\OpenapiSchema\Factories\SchemaFactory;
use Apie\SchemaGenerator\Context\SchemaContext;
use Apie\SchemaGenerator\Contracts\SchemaProvider;
use Apie\ObjectAccessNormalizer\ObjectAccess\ObjectAccessInterface;
use ReflectionClass;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;

class ObjectAccessSchemaProvider implements SchemaProvider
{
    /**
     * @var ObjectAccessInterface
     */
    private $objectAccess;
    /**
     * @var ClassMetadataFactoryInterface|null
     */
    private $classMetadataFactory;

    public function __construct(ObjectAccessInterface $objectAccess, ?ClassMetadataFactoryInterface $classMetadataFactory = null)
    {
        $this->objectAccess = $objectAccess;
        $this->classMetadataFactory = $classMetadataFactory;
    }

    public function supports(ReflectionClass $class, ?SchemaContext $schemaContext = null): bool
    {
        return true;
    }

    public function toSchema(ReflectionClass $class, ?SchemaContext $schemaContext = null): SchemaContract
    {
        $groups = $schemaContext ? $schemaContext->getGroups() : [];
        $schema = SchemaFactory::createObjectSchemaWithoutProperties($class, $schemaContext->getOperation(), $groups);
        $objectAccess = $this->filterObjectAccess($class->name, $schemaContext->getGroups());
        if ($schemaContext->getOperation() === 'get') {
            foreach ($objectAccess->getGetterFields($class) as $field) {
                $types = $objectAccess->getGetterTypes($class, $field);
            }
        }
        // TODO add write properties.
        return $schema;
    }

    /**
     * Adds FilteredObjectAccess decorator around the Object Access by reading the class metadata needed for the serializer.
     */
    private function filterObjectAccess(string $className, array $groups): ObjectAccessInterface
    {
        if ($this->classMetadataFactory) {
            return $this->objectAccess;
        }
        $allowedAttributes = [];
        foreach ($this->classMetadataFactory->getMetadataFor($className)->getAttributesMetadata() as $attributeMetadata) {
            $name = $attributeMetadata->getName();

            if (array_intersect($attributeMetadata->getGroups(), $groups)) {
                $allowedAttributes[] = $name;
            }
        }

        return new FilteredObjectAccess($this->objectAccess, $allowedAttributes);
    }
}