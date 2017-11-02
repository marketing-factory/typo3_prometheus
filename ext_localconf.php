<?php

$TYPO3_CONF_VARS['FE']['eID_include']['prometheus_metrices'] = 'EXT:prometheus/Classes/Eid/Metrics.php';


$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['prometheus']['metricesToMeasure'] = [
   // \Mfc\Prometheus\Services\Metrices\FeSessionsMetrics::class,
   // \Mfc\Prometheus\Services\Metrices\FeUsersMetrics::class,
   // \Mfc\Prometheus\Services\Metrices\BeSessionsMetrics::class,
   // \Mfc\Prometheus\Services\Metrices\BeUsersMetrics::class,
    \Mfc\Prometheus\Services\Metrices\PagesMetrics::class,
    \Mfc\Prometheus\Services\Metrices\TtContentMetrics::class,

   // \Mfc\Prometheus\Service\Metrices\SysDomainMetrics::class,
   // \Mfc\Prometheus\Service\Metrices\CfCachePagesMetrics::class,

];


$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] =
    \Mfc\Prometheus\Controller\MetricsCommandController::class;
