<?php

use Mfd\Prometheus\Middleware\MetricsHandler;

return [
    'frontend' => [
        'mfd/prometheus/metrics-middleware' => [
            'target' => MetricsHandler::class,
            'before' => [
                'typo3/cms-frontend/site',
            ],
        ],
    ],
];
