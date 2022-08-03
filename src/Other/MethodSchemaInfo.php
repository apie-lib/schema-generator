<?php
namespace Apie\SchemaGenerator\Other;

use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;

class MethodSchemaInfo
{
    /**
     * @var array<int|string, Schema|Reference>
     */
    public array $schemas = [];

    /**
     * @var string[]
     */
    public array $required = [];
}
