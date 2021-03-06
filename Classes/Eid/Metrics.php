<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017-2017 Simon Schmidt <typo3@marketing-factory.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;

$ipHelper = GeneralUtility::makeInstance(\Mfc\Prometheus\Services\IpAddressService::class);

if ($ipHelper->ipInAllowedRange()) {
    $metricController = GeneralUtility::makeInstance(\Mfc\Prometheus\Domain\Repository\MetricsRepository::class);

    $returnData = implode(PHP_EOL, array_keys($metricController->getAllMetrics()));

    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Content-Type: text/plain; charset=utf-8');

    echo $returnData . PHP_EOL;
} else {
    header('HTTP/1.0 403 Forbidden');
}


