<?php
namespace Apie\Tests\SchemaGenerator;

use Apie\Fixtures\Dto\DefaultExampleDto;
use Apie\Fixtures\Dto\EmptyDto;
use Apie\Fixtures\Dto\ExampleDto;
use Apie\Fixtures\Dto\NullableExampleDto;
use Apie\Fixtures\Dto\OptionalExampleDto;
use Apie\Fixtures\Entities\Polymorphic\Animal;
use Apie\Fixtures\Entities\Polymorphic\Cow;
use Apie\Fixtures\Entities\UserWithAddress;
use Apie\Fixtures\Enums\ColorEnum;
use Apie\Fixtures\Enums\EmptyEnum;
use Apie\Fixtures\Enums\IntEnum;
use Apie\Fixtures\Enums\NoValueEnum;
use Apie\SchemaGenerator\ComponentsBuilderFactory;
use cebe\openapi\spec\Discriminator;
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
        yield 'Backed string enum' => [
            new Schema([
                'type' => 'string',
                'enum' => ['red', 'green', 'blue'],
            ]),
            'ColorEnum-post',
            ColorEnum::class
        ];
        yield 'Empty Enum' => [
            new Schema([
                'type' => 'string',
                'enum' => [],
            ]),
            'EmptyEnum-post',
            EmptyEnum::class,
        ];
        yield 'Backed int Enum' => [
            new Schema([
                'type' => 'integer',
                'enum' => [0, 1, 2],
            ]),
            'IntEnum-post',
            IntEnum::class,
        ];
        yield 'Enum without values' => [
            new Schema([
                'type' => 'string',
                'enum' => ['RED', 'GREEN', 'BLUE'],
            ]),
            'NoValueEnum-post',
            NoValueEnum::class
        ];
        yield 'Empty DTO' => [
            new Schema([
                'type' => 'object',
                'properties' => [
                ],
                'required' => [],
            ]),
            'EmptyDto-post',
            EmptyDto::class,
        ];
        yield 'DTO with optional fields' => [
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
        yield 'DTO with required fields' => [
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
        yield 'DTO with nullable, required fields' => [
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
        yield 'DTO with nullable, optional fields' => [
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
        yield 'Entity with constructor arguments' => [
            new Schema([
                'type' => 'object',
                'required' => [
                    'address',
                ],
                'properties' => [
                    'address' => new Reference(['$ref' => 'AddressWithZipcodeCheck-post']),
                    'password' => new Reference(['$ref' => 'Password-post']),
                ],
            ]),
            'UserWithAddress-post',
            UserWithAddress::class,
        ];

        yield 'Polymorphic relation' => [
            new Schema([
                'oneOf' => [
                    new Reference(['$ref' => 'Cow-post']),
                    new Reference(['$ref' => 'Elephant-post']),
                    new Reference(['$ref' => 'Fish-post']),
                ],
                'discriminator' => new Discriminator([
                    'propertyName' => 'animalType',
                    'mapping' => [
                        'cow' => new Reference(['$ref' => 'Cow-post']),
                        'elephant' => new Reference(['$ref' => 'Elephant-post']),
                        'fish' => new Reference(['$ref' => 'Fish-post']),
                    ]
                ])
            ]),
            'Animal-post',
            Animal::class,
        ];

        yield 'Polymorphic relation - child' => [
            new Schema([
                'type' => 'object',
                'properties' => [
                    'id' => new Reference(['$ref' => 'AnimalIdentifier-post']),
                ],
                'required' => [],
            ]),
            'Cow-post',
            Cow::class,
        ];
    }
}
