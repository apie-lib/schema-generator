<?php
namespace Apie\Tests\SchemaGenerator;

use Apie\CommonValueObjects\Enums\Gender;
use Apie\CommonValueObjects\Identifiers\Slug;
use Apie\CommonValueObjects\Ranges\DateTimeRange;
use Apie\Fixtures\Enums\EmptyEnum;
use Apie\SchemaGenerator\ComponentsBuilderFactory;
use cebe\openapi\spec\Schema;
use PHPUnit\Framework\TestCase;

class ComponentsBuilderFactoryTest extends TestCase
{
    private function givenAComponentsBuilderFactory(): ComponentsBuilderFactory
    {
        return ComponentsBuilderFactory::createComponentsBuilderFactory();
    }

    /**
     * @test
     * @dataProvider valueObjectProviders
     */
    public function i_can_have_a_schema_of_value_object(
        Schema $expected,
        string $expectedKey,
        string $valueObjectClass
    ) {
        $testItem = $this->givenAComponentsBuilderFactory();
        $builder = $testItem->createComponentsBuilder();
        $builder->addCreationSchemaFor($valueObjectClass);
        $components = $builder->getComponents();
        $schemas = $components->schemas;
        $this->assertNotEmpty($schemas);
        $this->assertEquals($expectedKey, key($schemas));
        $actualSchema = current($schemas);
        if ($expected->pattern) {
            $expected->pattern = $actualSchema->pattern;
        }
        $this->assertEquals($expected, $actualSchema);
    }

    public function valueObjectProviders()
    {
        yield [
            new Schema([
                'type' => 'string',
                'format' => 'slug',
                'pattern' => 'yes'
            ]),
            'Slug-post', 
            Slug::class
        ];
        yield [
            new Schema([
                'type' => 'object',
                'properties' => [

                ]
            ]),
            'DateTimeRange-post', 
            DateTimeRange::class
        ];
        yield [
            new Schema([
                'type' => 'string',
                'enum' => [Gender::MALE->value, Gender::FEMALE->value],
            ]),
            'Gender-post', 
            Gender::class
        ];
        yield [
            new Schema([
                'type' => 'string',
                'enum' => [],
            ]),
            'EmptyEnum-post',
            EmptyEnum::class,
        ];
    }
}