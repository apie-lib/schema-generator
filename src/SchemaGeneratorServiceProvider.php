<?php
namespace Apie\SchemaGenerator;

use Apie\ServiceProviderGenerator\UseGeneratedMethods;
use Illuminate\Support\ServiceProvider;

/**
 * This file is generated with apie/service-provider-generator from file: schema_generator.yaml
 * @codecoverageIgnore
 * @phpstan-ignore
 */
class SchemaGeneratorServiceProvider extends ServiceProvider
{
    use UseGeneratedMethods;

    public function register()
    {
        $this->app->singleton(
            \Apie\SchemaGenerator\ComponentsBuilderFactory::class,
            function ($app) {
                return call_user_func(
                    'Apie\\SchemaGenerator\\ComponentsBuilderFactory::createComponentsBuilderFactory'
                
                );
                
            }
        );
        
    }
}
