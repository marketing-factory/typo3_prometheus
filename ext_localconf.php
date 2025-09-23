<?php

use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;

if (!defined('TYPO3')) {
    return;
}

$boot = static function (): void {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['prometheus'] ??= [];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['prometheus']['backend']
        ??= TransientMemoryBackend::class;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['prometheus_storage'] ??= [];
};
$boot();
unset($boot);
