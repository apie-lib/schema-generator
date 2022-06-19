<?php
namespace Apie\SchemaGenerator\Builders;

use Apie\Core\Exceptions\DuplicateIdentifierException;
use Apie\Core\ValueObjects\Utils;
use Apie\SchemaGenerator\Exceptions\ICanNotExtractASchemaFromClassException;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use ReflectionClass;

class ComponentsBuilder
{
    /**
     * @var SchemaProvider[]
     */
    private array $schemaProviders;

    private Components $components;

    public function __construct(SchemaProvider... $schemaProviders)
    {
        $this->schemaProviders = $schemaProviders;
        $this->components = new Components([]);
        $this->setSchema('mixed', new Schema(['nullable' => true]));
    }

    public function getMixedReference(): Reference
    {
        return new Reference(['$ref' => 'mixed']);
    }

    public function getComponents(): Components
    {
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

    public function addCreationSchemaFor(string $class): Reference|Schema
    {
        switch ($class) {
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
            return new Reference(['$ref' => $identifier]);
        }
        foreach ($this->schemaProviders as $schemaProvider) {
            if ($schemaProvider->supports($refl)) {
                $this->components = $schemaProvider->addCreationSchemaFor($this, $identifier, $refl);
                return new Reference(['$ref' => $identifier]);
            }
        }
        throw new ICanNotExtractASchemaFromClassException($refl->name);
    }
}
