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

    private $groups;

    private $recursion = 0;

    private $defined = [];

    private $operation;

    public function __construct(SchemaGeneratorContract $schemaGenerator, string $operation, array $groups)
    {
        $this->schemaGenerator = $schemaGenerator;
        $this->operation = $operation;
        $this->groups = $groups;
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

    /**
     * @return array
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @return int
     */
    public function getRecursion(): int
    {
        return $this->recursion;
    }

    /**
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }
}