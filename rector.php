<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PostRector\Rector\NameImportingPostRector;
use Ssch\TYPO3Rector\CodeQuality\General\ConvertImplicitVariablesToExplicitGlobalsRector;
use Ssch\TYPO3Rector\CodeQuality\General\ExtEmConfRector;
use Ssch\TYPO3Rector\Configuration\Typo3Option;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;

return RectorConfig::configure()
    ->withConfiguredRule(ExtEmConfRector::class, [
        ExtEmConfRector::ADDITIONAL_VALUES_TO_BE_REMOVED => []
    ])
    ->withPaths([
        __DIR__ . '/Classes',
        __DIR__ . '/Configuration',
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets()
    ->withPreparedSets(codeQuality: true)
    ->withAttributesSets(symfony: true, jms: true)
    ->withSets([
        Typo3LevelSetList::UP_TO_TYPO3_12,
    ])
    # To have a better analysis from PHPStan, we teach it here some more things
    ->withPHPStanConfigs([
        Typo3Option::PHPSTAN_FOR_RECTOR_PATH
    ])
    ->withRules([
        ConvertImplicitVariablesToExplicitGlobalsRector::class,
    ])
    # If you use importNames(), you should consider excluding some TYPO3 files.
    ->withSkip([
        // @see https://github.com/sabbelasichon/typo3-rector/issues/2536
        __DIR__ . '/**/Configuration/ExtensionBuilder/*',
        __DIR__ . '/**/node_modules/*',
        NameImportingPostRector::class => [
            'ext_localconf.php',
            'ext_tables.php',
            'ClassAliasMap.php',
            __DIR__ . '/**/Configuration/*.php',
            __DIR__ . '/**/Configuration/**/*.php',
        ]
    ]);
