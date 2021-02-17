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

    public function __construct(
        SchemaGeneratorContract $schemaGenerator,
        array $predefined = []
    ) {
        $this->schemaGenerator = $schemaGenerator;
        $this->predefined = $predefined;
    }

    public function create(string $operation): SchemaContext
    {
        $groups = ['base', $operation];
        switch ($operation) {
            case 'get':
                $groups[] = 'read';
                break;
            case 'post':
            case 'put':
                $groups[] = 'write';
        }
        $context = new SchemaContext($this->schemaGenerator, $operation, $groups);
        foreach ($this->predefined as $className => $schema) {
            $context->register(new ReflectionClass($className), $schema);
        }
        return $context;
    }
}