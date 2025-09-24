<?php

namespace Mfd\Prometheus;

use TYPO3\CMS\Core\Core\Environment;

final class Extension
{
    public static function loadVendorLibraries(): void
    {
        if (Environment::isComposerMode()) {
            return;
        }

        $vendorPhar = __DIR__ . '/../Resources/Private/Libs/vendor.phar';

        if (file_exists($vendorPhar)) {
            $vendorPhar = realpath($vendorPhar);
            require_once "phar://{$vendorPhar}/vendor/autoload.php";
        }
    }
}