<?php

declare(strict_types=1);

namespace Mfd\Prometheus\EventListener\Metrics;

use Mfd\Prometheus\Event\MetricsCollectingEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class Php
{
    public function __invoke(MetricsCollectingEvent $event): void
    {
        $registry = $event->getRegistry();

        // Memory usage metrics
        $gauge = $registry->getOrRegisterGauge(
            'php',
            'memory_usage_bytes',
            'Current memory usage in bytes'
        );
        $gauge->set(memory_get_usage(true));

        $gauge = $registry->getOrRegisterGauge(
            'php',
            'memory_peak_usage_bytes',
            'Peak memory usage in bytes'
        );
        $gauge->set(memory_get_peak_usage(true));
    }
}
