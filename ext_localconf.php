<?php

$TYPO3_CONF_VARS['FE']['eID_include']['prometheus_metrices'] = 'EXT:prometheus/Classes/Eid/Metrics.php';


$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['prometheus']['metricesToMeasure']['fast'] = [
   \Mfc\Prometheus\Services\Metrices\FeSessionsMetrics::class,
   \Mfc\Prometheus\Services\Metrices\FeUsersMetrics::class,
   \Mfc\Prometheus\Services\Metrices\BeSessionsMetrics::class,
   \Mfc\Prometheus\Services\Metrices\BeUsersMetrics::class,
   \Mfc\Prometheus\Services\Metrices\SysDomainMetrics::class,
   \Mfc\Prometheus\Services\Metrices\CfCachePagesMetrics::class,

];
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['prometheus']['metricesToMeasure']['slow'] = [
    \Mfc\Prometheus\Services\Metrices\PagesMetrics::class,
    \Mfc\Prometheus\Services\Metrices\TtContentMetrics::class,
];

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['prometheus']['metricesToMeasure']['medium'] = [

];


$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
    \Mfc\Prometheus\Controller\MetricsCommandController::class;
