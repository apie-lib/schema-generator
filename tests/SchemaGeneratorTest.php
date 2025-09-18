<?php
namespace Apie\Tests\SchemaGenerator;

use Apie\Core\RegexUtils;
use Apie\Fixtures\Entities\Polymorphic\IntegerType;
use Apie\Fixtures\Entities\Polymorphic\MixedTypes;
use Apie\Fixtures\Entities\Polymorphic\MixedTypesIdentifier;
use Apie\Fixtures\Entities\Polymorphic\StringType;
use Apie\SchemaGenerator\ComponentsBuilderFactory;
use Apie\SchemaGenerator\SchemaGenerator;
use cebe\openapi\spec\Discriminator;
use cebe\openapi\spec\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

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
        $idSchema = new Schema([
            'type' => 'string',
            'format' => 'mixedtypesidentifier',
            'pattern' => RegexUtils::removeDelimiters(MixedTypesIdentifier::getRegularExpression())
        ]);
        yield 'value object' => [$idSchema, MixedTypesIdentifier::class];
        $integerTypeSchema = new Schema([
            'type' => 'object',
            'required' => [
                'type',
                'id',
                'name',
                'value',
                'step',
                'nullableValue',
                'uniqueToInteger'
            ],
            'properties' => [
                'type' => new Schema(['type' => 'string', 'nullable' => false]),
                'id' => $idSchema,
                'name' => new Schema(['type' => 'integer', 'nullable' => false]),
                'value' => new Schema(['type' => 'integer', 'nullable' => false]),
                'step' => new Schema(['type' => 'integer', 'nullable' => false]),
                'nullableValue' => new Schema(['type' => 'integer', 'nullable' => true]),
                'uniqueToInteger' => new Schema(['type' => 'integer', 'nullable' => true]),
            ],
        ]);
        yield 'polymorphic entity, IntegerType' => [$integerTypeSchema, IntegerType::class];
        $stringTypeSchema = new Schema([
            'type' => 'object',
            'required' => [
                'type',
                'id',
                'name',
                'value',
                'step',
                'nullableValue',
                'uniqueToString'
            ],
            'properties' => [
                'type' => new Schema(['type' => 'string', 'nullable' => false]),
                'id' => $idSchema,
                'name' => new Schema(['type' => 'string', 'nullable' => false]),
                'value' => new Schema(['type' => 'string', 'nullable' => false]),
                'step' => new Schema(['type' => 'string', 'nullable' => false]),
                'nullableValue' => new Schema(['type' => 'string', 'nullable' => true]),
                'uniqueToString' => new Schema(['type' => 'string', 'nullable' => true]),
            ],
        ]);
        yield 'polymorphic entity, StringType' => [$stringTypeSchema, StringType::class];
        $stringOrInt = new Schema([
            'oneOf' => [
                new Schema(['type' => 'string']),
                new Schema(['type' => 'integer']),
            ],
            'nullable' => false,
        ]);
        // Schema clone is a shallow clone, but we need a deep clone
        $integerTypeSchema = unserialize(serialize($integerTypeSchema));
        $stringTypeSchema = unserialize(serialize($stringTypeSchema));
        $type = $integerTypeSchema->properties['type'];
        $type->enum = ['integer'];
        $type = $stringTypeSchema->properties['type'];
        $type->enum = ['string'];

        yield 'polymorphic entity' => [
            new Schema([
                'type' => 'object',
                'properties' => [
                    'type' => new Schema(['type' => 'string', 'nullable' => false, 'enum' => ['integer', 'string']]),
                    'id' => $idSchema,
                    'name' => $stringOrInt,
                    'value' => $stringOrInt,
                    'step' => $stringOrInt,
                    'nullableValue' => new Schema([
                        'oneOf' => [
                            new Schema(['type' => 'string']),
                            new Schema(['type' => 'integer']),
                            new Schema(['nullable' => true, 'default' => null]),
                        ],
                        'nullable' => true,
                    ]),
                    'uniqueToInteger' => new Schema(['type' => 'integer', 'nullable' => true]),
                    'uniqueToString' => new Schema(['type' => 'string', 'nullable' => true]),
                ],
                'required' => [
                    'type',
                ],
                'oneOf' => [
                    $integerTypeSchema,
                    $stringTypeSchema
                ],
               'discriminator' => new Discriminator([
                  'propertyName' => 'type',
                  'mapping' => [
                      'integer' => '#/components/schemas/IntegerType-post',
                      'string' => '#/components/schemas/StringType-post',
                  ],
               ])
            ]),
            MixedTypes::class,
        ];
    }
}
