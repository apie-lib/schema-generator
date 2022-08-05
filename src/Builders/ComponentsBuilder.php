<?php
namespace Apie\SchemaGenerator\Builders;

use Apie\Core\Exceptions\DuplicateIdentifierException;
use Apie\Core\ValueObjects\Utils;
use Apie\SchemaGenerator\Exceptions\ICanNotExtractASchemaFromClassException;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use Apie\SchemaGenerator\Other\MethodSchemaInfo;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

class ComponentsBuilder
{
    /**
     * @var array<int, SchemaProvider<object>>
     */
    private array $schemaProviders;

    private Components $components;

    /**
     * @param SchemaProvider<object> $schemaProviders
     */
    public function __construct(SchemaProvider... $schemaProviders)
    {
        $this->schemaProviders = $schemaProviders;
        $this->components = new Components([]);
    }

    public function getMixedReference(): Reference
    {
        if (!isset($this->components->schemas['mixed'])) {
            $this->setSchema('mixed', new Schema(['nullable' => true]));
        }
        return new Reference(['$ref' => '#/components/schemas/mixed']);
    }

    public function getComponents(): Components
    {
        $schemas = $this->components->schemas;
        ksort($schemas);
        $this->components->schemas = $schemas;
        return $this->components;
    }

    public function setSchema(string $identifier, Schema $schema): self
    {
        if (isset($this->components->schemas[$identifier])) {
            throw new DuplicateIdentifierException($identifier);
        }
        $schemas = $this->components->schemas;
        $schemas[$identifier] = $schema;
        
        $this->components->schemas = $schemas;
        return $this;
    }

    public function getSchemaForMethod(ReflectionMethod $method): MethodSchemaInfo
    {
        $returnValue = new MethodSchemaInfo();
        foreach ($method->getParameters() as $parameter) {
            if (!$parameter->isDefaultValueAvailable() && !$parameter->allowsNull()) {
                $returnValue->required[] = $parameter->name;
            }
            $type = $parameter->getType();
            $returnValue->schemas[$parameter->name] = $this->getSchemaForType($type, $parameter->isVariadic());
        }
        return $returnValue;
    }

    public function getSchemaForType(ReflectionType|null $type, bool $array = false, bool $display = false): Schema|Reference
    {
        $methodName = $display ? 'addDisplaySchemaFor' : 'addCreationSchemaFor';
        $result = $this->getMixedReference();
        if ($type instanceof ReflectionIntersectionType) {
            $allOfs = [];
            foreach ($type->getTypes() as $allOfType) {
                $allOfs[] = $this->$methodName((string) $allOfType);
            }
            $result = new Schema([
                'allOf' => $allOfs,
            ]);
        } elseif ($type instanceof ReflectionUnionType) {
            $oneOfs = [];
            foreach ($type->getTypes() as $oneOfType) {
                $oneOfs[] = $this->$methodName((string) $oneOfType);
            }
            $result = new Schema([
                'oneOf' => $oneOfs,
            ]);
        } elseif ($type instanceof ReflectionNamedType) {
            $result = $this->$methodName($type->getName());
        }
        if ($array) {
            return new Schema([
                'type' => 'array',
                'items' => $result,
            ]);
        }
        return $result;
    }

    public function addDisplaySchemaFor(string $class, ?string $discriminatorColumn = null): Reference|Schema
    {
        switch ($class) {
            case 'mixed':
                return $this->getMixedReference();
            case 'string':
            case 'bool':
                return new Schema(['type' => $class]);
            case 'int':
                return new Schema(['type' => 'integer']);
            case'float':
            case 'double':
                return new Schema(['type' => 'number']);
        }
        $refl = new ReflectionClass($class);
        $identifier = Utils::getDisplayNameForValueObject($refl) . '-get';
        if (isset($this->components->schemas[$identifier])) {
            return new Reference(['$ref' => '#/components/schemas/' . $identifier]);
        }
        foreach ($this->schemaProviders as $schemaProvider) {
            if ($schemaProvider->supports($refl)) {
                $this->components = $schemaProvider->addDisplaySchemaFor($this, $identifier, $refl);
                return new Reference(['$ref' => '#/components/schemas/' . $identifier]);
            }
        }
        throw new ICanNotExtractASchemaFromClassException($refl->name);
    }

    public function addCreationSchemaFor(string $class, ?string $discriminatorColumn = null): Reference|Schema
    {
        switch ($class) {
            case 'mixed':
                return $this->getMixedReference();
            case 'string':
            case 'bool':
                return new Schema(['type' => $class]);
            case 'int':
                return new Schema(['type' => 'integer']);
            case'float':
            case 'double':
                return new Schema(['type' => 'number']);
        }
        $refl = new ReflectionClass($class);
        $identifier = Utils::getDisplayNameForValueObject($refl) . '-post';
        if (isset($this->components->schemas[$identifier])) {
            return new Reference(['$ref' => '#/components/schemas/' . $identifier]);
        }
        foreach ($this->schemaProviders as $schemaProvider) {
            if ($schemaProvider->supports($refl)) {
                $this->components = $schemaProvider->addCreationSchemaFor($this, $identifier, $refl);
                return new Reference(['$ref' => '#/components/schemas/' . $identifier]);
            }
        }
        throw new ICanNotExtractASchemaFromClassException($refl->name);
    }
}
