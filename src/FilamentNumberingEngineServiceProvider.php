<?php

namespace Wezlo\FilamentNumberingEngine;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wezlo\FilamentNumberingEngine\Services\NumberingEngine;
use Wezlo\FilamentNumberingEngine\Services\PatternParser;

class FilamentNumberingEngineServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-numbering-engine';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasMigrations([
                'create_numbering_sequences_table',
                'create_numbering_counters_table',
            ])
            ->hasTranslations();
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(PatternParser::class, function () {
            $parser = new PatternParser;

            foreach (config('filament-numbering-engine.custom_resolvers', []) as $resolverClass) {
                $parser->registerResolver(app($resolverClass));
            }

            return $parser;
        });

        $this->app->singleton(NumberingEngine::class);
    }
}
