<?php

$TYPO3_CONF_VARS['FE']['eID_include']['prometheus_metrics'] = 'EXT:prometheus/Classes/Eid/Metrics.php';


$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['prometheus']['metricsToMeasure']['fast'] = [
   \Mfc\Prometheus\Services\Metrics\FeSessionsMetrics::class,
   \Mfc\Prometheus\Services\Metrics\FeUsersMetrics::class,
   \Mfc\Prometheus\Services\Metrics\BeSessionsMetrics::class,
   \Mfc\Prometheus\Services\Metrics\BeUsersMetrics::class,
   \Mfc\Prometheus\Services\Metrics\SysDomainMetrics::class,
   \Mfc\Prometheus\Services\Metrics\CfCachePagesMetrics::class,

];
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['prometheus']['metricsToMeasure']['slow'] = [
    \Mfc\Prometheus\Services\Metrics\PagesMetrics::class,
    \Mfc\Prometheus\Services\Metrics\TtContentMetrics::class,
];

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['prometheus']['metricsToMeasure']['medium'] = [

];


$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
    \Mfc\Prometheus\Controller\MetricsCommandController::class;
