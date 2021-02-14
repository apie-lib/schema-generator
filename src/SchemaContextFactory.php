<?php


namespace Apie\SchemaGenerator;

use Apie\SchemaGenerator\Contracts\SchemaContextFactoryContract;
use Apie\SchemaGenerator\Context\SchemaContext;
use Apie\SchemaGenerator\Contracts\SchemaGeneratorContract;
use ReflectionClass;

class SchemaContextFactory implements SchemaContextFactoryContract
{
    private $schemaGenerator;

    private $predefined;

    public function __construct(SchemaGeneratorContract $schemaGenerator, array $predefined = [])
    {
        $this->schemaGenerator = $schemaGenerator;
        $this->predefined = $predefined;
    }

    public function create(): SchemaContext
    {
        $context = new SchemaContext($this->schemaGenerator);
        foreach ($this->predefined as $className => $schema) {
            $context->register(new ReflectionClass($className), $schema);
        }
        return $context;
    }
}