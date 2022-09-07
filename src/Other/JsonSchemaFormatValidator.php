<?php
namespace Apie\SchemaGenerator\Other;

use Exception;

class JsonSchemaFormatValidator
{
    public function __construct(private string $className)
    {
    }

    public function __invoke(string $value): bool
    {
        try {
            new $this->className($value);
            return true;
        } catch (Exception $error) {
            return false;
        }
    }
}
