<?php


namespace Apie\SchemaGenerator\Contracts;


use Apie\SchemaGenerator\Context\SchemaContext;

interface SchemaContextFactoryContract
{
    public function create(string $operation): SchemaContext;
}