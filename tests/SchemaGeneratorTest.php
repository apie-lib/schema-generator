<?php
namespace Apie\Tests\SchemaGenerator;

use Apie\SchemaGenerator\ComponentsBuilderFactory;
use Apie\SchemaGenerator\SchemaGenerator;
use cebe\openapi\spec\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SchemaGeneratorTest extends TestCase
{
    #[Test]
    #[DataProvider('schemaProvider')]
    public function it_can_return_a_schema(Schema $expected, string $input)
    {
        $testItem = new SchemaGenerator(ComponentsBuilderFactory::createComponentsBuilderFactory());
        $actual = $testItem->createSchema($input);
        
        $this->assertEquals(
            $expected,
            $actual
        );
    }

    public static function schemaProvider(): \Generator
    {
        yield 'string primitive' => [new Schema(['type' => 'string']), 'string'];
        yield 'string array' => [new Schema(['type' => 'array', 'items' => new Schema(['type' => 'string'])]), 'string[]'];
    }
}
