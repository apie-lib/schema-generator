<?php
namespace Apie\SchemaGenerator\Builders;

use Apie\Core\Attributes\Context;
use Apie\Core\Exceptions\DuplicateIdentifierException;
use Apie\Core\ValueObjects\Utils;
use Apie\SchemaGenerator\Exceptions\ICanNotExtractASchemaFromClassException;
use Apie\SchemaGenerator\Interfaces\ModifySchemaProvider;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use Apie\SchemaGenerator\Other\MethodSchemaInfo;
use cebe\openapi\ReferenceContext;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\OpenApi;
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

    public function getSchemaForReference(Reference $reference): ?Schema
    {
        $result = $reference->resolve(
            new ReferenceContext(
                new OpenApi(['components' => $this->components]),
                'file:///#/components'
            )
        );
        assert($result === null || $result instanceof Schema);
        return $result;
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
            if (count($parameter->getAttributes(Context::class)) > 0) {
                continue;
            }
            if (!$parameter->isDefaultValueAvailable() && !$parameter->allowsNull()) {
                $returnValue->required[] = $parameter->name;
            }
            $type = $parameter->getType();
            $returnValue->schemas[$parameter->name] = $this->getSchemaForType($type, $parameter->isVariadic(), nullable: $type?->allowsNull() ?? false);
        }
        return $returnValue;
    }

    public function getSchemaForType(ReflectionType|null $type, bool $array = false, bool $display = false, bool $nullable = false): Schema|Reference
    {
        $map = $nullable ? ['nullable' => true] : [];
        $methodName = $display ? 'addDisplaySchemaFor' : 'addCreationSchemaFor';
        $result = $this->getMixedReference();
        if ($type instanceof ReflectionIntersectionType) {
            $allOfs = [];
            foreach ($type->getTypes() as $allOfType) {
                $allOfs[] = $this->$methodName((string) $allOfType, nullable: $nullable || $allOfType->allowsNull());
            }
            $result = new Schema([
                'allOf' => $allOfs,
            ] + $map);
        } elseif ($type instanceof ReflectionUnionType) {
            $oneOfs = [];
            foreach ($type->getTypes() as $oneOfType) {
                $oneOfs[] = $this->$methodName((string) $oneOfType, nullable: $nullable || $oneOfType->allowsNull());
            }
            $result = new Schema([
                'oneOf' => $oneOfs,
            ] + $map);
        } elseif ($type instanceof ReflectionNamedType) {
            $result = $this->$methodName($type->getName(), nullable: $nullable || $type->allowsNull());
        }
        if ($array) {
            return new Schema([
                'type' => 'array',
                'items' => $result,
            ] + $map);
        }
        return $result;
    }

    public function addDisplaySchemaFor(string $class, ?string $discriminatorColumn = null, bool $nullable = false): Reference|Schema
    {
        $map = $nullable ? ['nullable' => true] : [];
        switch ($class) {
            case 'mixed':
                return $this->getMixedReference();
            case 'string':
                return new Schema(['type' => $class] + $map);
            case 'array':
                return new Schema(['type' => 'object', 'additionalProperties' => $this->getMixedReference()] + $map);
            case 'bool':
                return new Schema(['type' => 'boolean'] + $map);
            case 'int':
                return new Schema(['type' => 'integer'] + $map);
            case'float':
            case 'double':
                return new Schema(['type' => 'number'] + $map);
            case 'void':
            case 'null':
                return new Schema(['nullable' => true, 'default' => null]);
        }
        $refl = new ReflectionClass($class);
        $identifier = Utils::getDisplayNameForValueObject($refl) . ($nullable ? '-nullable' : '') . '-get';
        if (isset($this->components->schemas[$identifier])) {
            return new Reference(['$ref' => '#/components/schemas/' . $identifier]);
        }
        foreach ($this->schemaProviders as $schemaProvider) {
            if ($schemaProvider->supports($refl)) {
                $this->components = $schemaProvider->addDisplaySchemaFor($this, $identifier, $refl, $nullable);
                return new Reference(['$ref' => '#/components/schemas/' . $identifier]);
            }
        }
        throw new ICanNotExtractASchemaFromClassException($refl->name);
    }

    public function addCreationSchemaFor(string $class, ?string $discriminatorColumn = null, bool $nullable = false): Reference|Schema
    {
        $map = $nullable ? ['nullable' => true] : [];
        switch ($class) {
            case 'mixed':
                return $this->getMixedReference();
            case 'object':
                return new Schema(['type' => 'object', 'additionalProperties' => true] + $map);
            case 'string':
                return new Schema(['type' => $class] + $map);
            case 'array':
                return new Schema(['type' => 'object', 'additionalProperties' => $this->getMixedReference()] + $map);    
            case 'bool':
                return new Schema(['type' => 'boolean'] + $map);
            case 'int':
                return new Schema(['type' => 'integer'] + $map);
            case'float':
            case 'double':
                return new Schema(['type' => 'number'] + $map);
            case 'null':
                return new Schema(['nullable' => true, 'default' => null]);
        }
        $refl = new ReflectionClass($class);
        $identifier = Utils::getDisplayNameForValueObject($refl) . ($nullable ? '-nullable' : '') . '-post';
        if (isset($this->components->schemas[$identifier])) {
            return new Reference(['$ref' => '#/components/schemas/' . $identifier]);
        }
        foreach ($this->schemaProviders as $schemaProvider) {
            if ($schemaProvider->supports($refl)) {
                $this->components = $schemaProvider->addCreationSchemaFor($this, $identifier, $refl, $nullable);
                return new Reference(['$ref' => '#/components/schemas/' . $identifier]);
            }
        }
        throw new ICanNotExtractASchemaFromClassException($refl->name);
    }

    public function addModificationSchemaFor(string $class, ?string $discriminatorColumn = null): Reference|Schema
    {
        $refl = new ReflectionClass($class);
        $identifier = Utils::getDisplayNameForValueObject($refl) . '-patch';
        if (isset($this->components->schemas[$identifier])) {
            return new Reference(['$ref' => '#/components/schemas/' . $identifier]);
        }
        foreach ($this->schemaProviders as $schemaProvider) {
            if ($schemaProvider instanceof ModifySchemaProvider && $schemaProvider->supports($refl)) {
                $this->components = $schemaProvider->addModificationSchemaFor($this, $identifier, $refl);
                return new Reference(['$ref' => '#/components/schemas/' . $identifier]);
            }
        }
        throw new ICanNotExtractASchemaFromClassException($refl->name);
    }
}
