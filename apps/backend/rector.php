<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/packages/outbox-bundle/src'])
    ->withSkipPath(__DIR__ . '/src/Kernel.php')

    // PHP 8.4 upgrade rules
    ->withPhpSets(php84: true)

    // Symfony rules auto-detected from composer.json
    ->withComposerBased(symfony: true)

    // Convert doctrine/symfony annotations to PHP 8 attributes
    ->withAttributesSets(symfony: true, doctrine: true)

    // Quality sets
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        instanceOf: true,
        earlyReturn: true,
        symfonyCodeQuality: true,
        symfonyConfigs: true,
    )

    // Cache for faster subsequent runs
    ->withCache(cacheDirectory: __DIR__ . '/var/cache/rector');
