<?php


namespace Apie\SchemaGenerator\Context;

use Apie\Core\Exceptions\NameAlreadyDefinedException;
use Apie\OpenapiSchema\Contract\SchemaContract;
use Apie\SchemaGenerator\Contracts\SchemaGeneratorContract;
use Apie\SchemaGenerator\SchemaGenerator;
use ReflectionClass;

class SchemaContext
{
    private $schemaGenerator;

    private $defined = [];

    public function __construct(SchemaGeneratorContract $schemaGenerator)
    {
        $this->schemaGenerator = $schemaGenerator;
    }

    public function register(ReflectionClass $class, ?SchemaContract $schema = null)
    {
        if (isset($this->defined[$class->name])) {
            throw new NameAlreadyDefinedException($class->name);
        }
        if ($schema === null) {
            $schema = $this->schemaGenerator->fromClassToSchema($class);
        }
        $this->defined[$class->name] = $schema;
    }
}