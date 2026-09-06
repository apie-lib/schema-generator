<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use BcMath\Number;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Schema;
use Closure;
use DateInterval;
use DOMAttr;
use DOMElement;
use FFI\CData;
use FFI\CType;
use GMP;
use ReflectionClass;
use SimpleXMLElement;
use StreamBucket;
use Uri\Rfc3986\Uri;

/**
 * @implements SchemaProvider<Uri|GMP|Number>
 */
class PredefinedObjectSchemaProvider implements SchemaProvider
{
    public function supports(ReflectionClass $class): bool
    {
        return in_array(
            $class->name,
            [
                Uri::class,
                Number::class,
                GMP::class,
                StreamBucket::class,
                CType::class,
                CData::class,
                SimpleXMLElement::class,
                DOMAttr::class,
                DOMElement::class,
                DateInterval::class,
                Closure::class,
            ]
        );
    }

    /**
     * @param ReflectionClass<covariant object> $class
     */
    private function createSchema(ReflectionClass $class): Schema
    {
        $fixturePath = __DIR__ . '/../../fixtures/' . str_replace('\\', '_', $class->name) . '.json';
        $data = json_decode(file_get_contents($fixturePath), true, flags: JSON_THROW_ON_ERROR);
        assert(is_array($data));
        return new Schema($data);
    }

    public function addDisplaySchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class,
        bool $nullable = false
    ): Components {
        $schema = $this->createSchema($class);
        if ($nullable) {
            $schema->nullable = true;
        }
        $componentsBuilder->setSchema($componentIdentifier, $schema);

        return $componentsBuilder->getComponents();
    }

    public function addCreationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class,
        bool $nullable = false
    ): Components {
        $schema = $this->createSchema($class);
        if ($nullable) {
            $schema->nullable = true;
        }
        $componentsBuilder->setSchema($componentIdentifier, $schema);

        return $componentsBuilder->getComponents();
    }
}
