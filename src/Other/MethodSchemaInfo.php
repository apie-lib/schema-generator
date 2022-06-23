<?php
namespace Apie\SchemaGenerator\Other;

class MethodSchemaInfo
{
    /**
     * @var (Schema|Reference)[]
     */
    public array $schemas = [];

    /**
     * @var string[]
     */
    public array $required = [];
}
