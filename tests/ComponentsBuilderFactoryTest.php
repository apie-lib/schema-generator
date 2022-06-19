<?php
namespace Apie\Tests\SchemaGenerator;

use Apie\CommonValueObjects\Enums\Gender;
use Apie\CommonValueObjects\Identifiers\Slug;
use Apie\CommonValueObjects\Ranges\DateTimeRange;
use Apie\Fixtures\Dto\DefaultExampleDto;
use Apie\Fixtures\Dto\EmptyDto;
use Apie\Fixtures\Dto\ExampleDto;
use Apie\Fixtures\Dto\NullableExampleDto;
use Apie\Fixtures\Dto\OptionalExampleDto;
use Apie\Fixtures\Enums\EmptyEnum;
use Apie\SchemaGenerator\ComponentsBuilderFactory;
use cebe\openapi\spec\Reference;
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
        $this->assertArrayHasKey($expectedKey, $schemas);
        $actualSchema = $schemas[$expectedKey];
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
                    'start' => new Reference(['$ref' => 'DateWithTimezone-post']),
                    'end' => new Reference(['$ref' => 'DateWithTimezone-post']),
                ],
                'required' => ['start', 'end'],
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
        yield [
            new Schema([
                'type' => 'object',
                'properties' => [
                ],
                'required' => [],
            ]),
            'EmptyDto-post',
            EmptyDto::class,
        ];
        yield [
            new Schema([
                'type' => 'object',
                'required' => [],
                'properties' => [
                    'string' => new Schema(['type' => 'string', 'nullable' => false]),
                    'integer' => new Schema(['type' => 'integer', 'nullable' => false]),
                    'floatingPoint' => new Schema(['type' => 'number', 'nullable' => false]),
                    'trueOrFalse' => new Schema(['type' => 'bool', 'nullable' => false]),
                    'mixed' => new Reference(['$ref' => 'mixed']),
                    'noType' => new Reference(['$ref' => 'mixed']),
                    'gender' => new Reference(['$ref' => 'Gender-post']),
                ],
            ]),
            'DefaultExampleDto-post',
            DefaultExampleDto::class,
        ];
        yield [
            new Schema([
                'type' => 'object',
                'required' => [
                    'string',
                    'integer',
                    'floatingPoint',
                    'trueOrFalse',
                    'mixed',
                    'noType',
                    'gender',
                ],
                'properties' => [
                    'string' => new Schema(['type' => 'string', 'nullable' => false]),
                    'integer' => new Schema(['type' => 'integer', 'nullable' => false]),
                    'floatingPoint' => new Schema(['type' => 'number', 'nullable' => false]),
                    'trueOrFalse' => new Schema(['type' => 'bool', 'nullable' => false]),
                    'mixed' => new Reference(['$ref' => 'mixed']),
                    'noType' => new Reference(['$ref' => 'mixed']),
                    'gender' => new Reference(['$ref' => 'Gender-post']),
                ],
            ]),
            'ExampleDto-post',
            ExampleDto::class,
        ];
        yield [
            new Schema([
                'type' => 'object',
                'required' => [
                    'nullableString',
                    'nullableInteger',
                    'nullableFloatingPoint',
                    'nullableTrueOrFalse',
                    'nullableGender',
                ],
                'properties' => [
                    'nullableString' => new Schema(['type' => 'string', 'nullable' => true]),
                    'nullableInteger' => new Schema(['type' => 'integer', 'nullable' => true]),
                    'nullableFloatingPoint' => new Schema(['type' => 'number', 'nullable' => true]),
                    'nullableTrueOrFalse' => new Schema(['type' => 'bool', 'nullable' => true]),
                    'nullableGender' => new Reference(['$ref' => 'Gender-post']),
                ],
            ]),
            'NullableExampleDto-post',
            NullableExampleDto::class,
        ];
        yield [
            new Schema([
                'type' => 'object',
                'required' => [
                ],
                'properties' => [
                    'optionalString' => new Schema(['type' => 'string', 'nullable' => true]),
                    'optionalInteger' => new Schema(['type' => 'integer', 'nullable' => true]),
                    'optionalFloatingPoint' => new Schema(['type' => 'number', 'nullable' => true]),
                    'optionalTrueOrFalse' => new Schema(['type' => 'bool', 'nullable' => true]),
                    'mixed' => new Reference(['$ref' => 'mixed']),
                    'noType' => new Reference(['$ref' => 'mixed']),
                    'optionalGender' => new Reference(['$ref' => 'Gender-post']),
                ],
            ]),
            'OptionalExampleDto-post',
            OptionalExampleDto::class,
        ];
    }
}
