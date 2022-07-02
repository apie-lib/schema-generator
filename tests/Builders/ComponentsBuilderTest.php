<?php
namespace Apie\Tests\SchemaGenerator\Builders;

use Apie\Fixtures\ValueObjects\IsStringValueObjectExample;
use Apie\Fixtures\ValueObjects\Password;
use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Exceptions\ICanNotExtractASchemaFromClassException;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Schema;
use PHPUnit\Framework\TestCase;

class ComponentsBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_only_provide_simple_types_or_defined_schemas_with_no_schema_providers()
    {
        $testItem = new ComponentsBuilder();
        $testItem->setSchema('Password-post', new Schema(['type' => 'object']));
        $this->assertEquals(new Reference(['$ref' => 'Password-post']), $testItem->addCreationSchemaFor(Password::class));
        $this->assertEquals(new Schema(['type' => 'string']), $testItem->addCreationSchemaFor('string'));
        $this->assertEquals(new Schema(['type' => 'bool']), $testItem->addCreationSchemaFor('bool'));
        $this->assertEquals(new Schema(['type' => 'integer']), $testItem->addCreationSchemaFor('int'));
        $this->assertEquals(new Schema(['type' => 'number']), $testItem->addCreationSchemaFor('float'));
        $this->expectException(ICanNotExtractASchemaFromClassException::class);
        $testItem->addCreationSchemaFor(IsStringValueObjectExample::class);
    }
}
