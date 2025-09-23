<?php

namespace Mfd\Prometheus\EventListener\Metrics;

use Mfd\Prometheus\Event\MetricsCollectingEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class Php
{
    public function __invoke(MetricsCollectingEvent $event): void
    {
        // Memory usage metrics
        $gauge = $event->getRegistry()->getOrRegisterGauge(
            'typo3',
            'memory_usage_bytes',
            'Current memory usage in bytes'
        );
        $gauge->set(memory_get_usage(true));

        $gauge = $event->getRegistry()->getOrRegisterGauge(
            'typo3',
            'memory_peak_usage_bytes',
            'Peak memory usage in bytes'
        );
        $gauge->set(memory_get_peak_usage(true));
    }
}
