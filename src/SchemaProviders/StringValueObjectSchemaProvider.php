<?php
namespace Apie\SchemaGenerator\SchemaProviders;

use Apie\Core\RegexUtils;
use Apie\Core\ValueObjects\Interfaces\HasRegexValueObjectInterface;
use Apie\Core\ValueObjects\Interfaces\LimitedOptionsInterface;
use Apie\Core\ValueObjects\Interfaces\StringValueObjectInterface;
use Apie\Core\ValueObjects\Interfaces\ValueObjectInterface;
use Apie\Core\ValueObjects\Utils;
use Apie\SchemaGenerator\Builders\ComponentsBuilder;
use Apie\SchemaGenerator\Interfaces\SchemaProvider;
use Apie\SchemaGenerator\Other\JsonSchemaFormatValidator;
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Schema;
use League\OpenAPIValidation\Schema\TypeFormats\FormatsContainer;
use ReflectionClass;

/**
 * @implements SchemaProvider<StringValueObjectInterface>
 */
class StringValueObjectSchemaProvider implements SchemaProvider
{
    public function __construct(
        private readonly int $maxEnumSize = 100
    ) {
    }

    public function supports(ReflectionClass $class): bool
    {
        if (!in_array(ValueObjectInterface::class, $class->getInterfaceNames())) {
            return false;
        }
        $returnType = (string) $class->getMethod('toNative')->getReturnType();
        return $returnType === 'string' || $returnType === '?string';
    }

    public function addDisplaySchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class,
        bool $nullable = false
    ): Components {
        return $this->addCreationSchemaFor($componentsBuilder, $componentIdentifier, $class, $nullable);
    }

    public function addCreationSchemaFor(
        ComponentsBuilder $componentsBuilder,
        string $componentIdentifier,
        ReflectionClass $class,
        bool $nullable = false
    ): Components {
        $format = strtolower(Utils::getDisplayNameForValueObject($class));
        if (class_exists(FormatsContainer::class) && !FormatsContainer::getFormat('string', $format)) {
            FormatsContainer::registerFormat('string', $format, new JsonSchemaFormatValidator($class->name));
        }
        $schema = new Schema([
            'type' => 'string',
            'format' => $format,
        ]);
        ComponentsBuilder::addDescriptionOfObject($schema, $class);
        if (in_array(HasRegexValueObjectInterface::class, $class->getInterfaceNames())) {
            $className = $class->name;
            $schema->pattern = RegexUtils::removeDelimiters($className::getRegularExpression());
        }
        if ($nullable) {
            $schema->nullable = true;
        }
        if (in_array(LimitedOptionsInterface::class, $class->getInterfaceNames())) {
            /** @var class-string<LimitedOptionsInterface> $className */
            $className = $class->name;
            $options = $className::getOptions();
            if (count($options) > 0 && count($options) <= $this->maxEnumSize) {
                $schema->enum = $options->toArray();
            }
        }
        $componentsBuilder->setSchema($componentIdentifier, $schema);

        return $componentsBuilder->getComponents();
    }
}
