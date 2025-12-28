<?php

namespace Eru\AvroPhonetic;

use Illuminate\Support\ServiceProvider;

class AvroPhoneticServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/avro-phonetic.php',
            'avro-phonetic'
        );

        // Register the main class as a singleton
        $this->app->singleton(Avro::class, function ($app) {
            $grammarPath = config('avro-phonetic.grammar_path');
            
            if ($grammarPath && file_exists($grammarPath)) {
                return Avro::fromGrammarFile($grammarPath);
            }
            
            return new Avro();
        });

        // Alias for easier resolution
        $this->app->alias(Avro::class, 'avro');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/avro-phonetic.php' => config_path('avro-phonetic.php'),
        ], 'avro-phonetic-config');

        // Publish grammar file
        $this->publishes([
            __DIR__ . '/../resources/grammar.json' => resource_path('avro/grammar.json'),
        ], 'avro-phonetic-grammar');

        // Publish all
        $this->publishes([
            __DIR__ . '/../config/avro-phonetic.php' => config_path('avro-phonetic.php'),
            __DIR__ . '/../resources/grammar.json' => resource_path('avro/grammar.json'),
        ], 'avro-phonetic');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            Avro::class,
            'avro',
        ];
    }
}
