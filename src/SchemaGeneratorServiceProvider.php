<?php
namespace Apie\SchemaGenerator;

use Apie\ServiceProviderGenerator\UseGeneratedMethods;
use Illuminate\Support\ServiceProvider;

/**
 * This file is generated with apie/service-provider-generator from file: schema_generator.yaml
 * @codeCoverageIgnore
 */
class SchemaGeneratorServiceProvider extends ServiceProvider
{
    use UseGeneratedMethods;

    public function register()
    {
        $this->registerSingleton(
            \Apie\SchemaGenerator\ComponentsBuilderFactory::class,
            function ($app) {
                return call_user_func(
                    'Apie\\SchemaGenerator\\ComponentsBuilderFactory::createComponentsBuilderFactory',
                    $this->parseArgument('%apie.open_api.max_enum_size%')
                );
                
            }
        );
        $this->registerSingleton(
            \Apie\SchemaGenerator\SchemaGenerator::class,
            function ($app) {
                return new \Apie\SchemaGenerator\SchemaGenerator(
                    $app->make(\Apie\SchemaGenerator\ComponentsBuilderFactory::class)
                );
            }
        );
        
    }
}
