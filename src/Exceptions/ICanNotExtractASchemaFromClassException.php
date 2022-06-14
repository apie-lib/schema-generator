<?php
namespace Apie\SchemaGenerator\Exceptions;

use RuntimeException;

class ICanNotExtractASchemaFromClassException extends RuntimeException
{
    public function __construct(string $className)
    {
        parent::__construct(
            sprintf(
                'I can not extract an OpenAPI Schema from class "%s"',
                $className
            )
        );
    }
}
