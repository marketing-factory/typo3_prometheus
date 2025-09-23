<?php

declare(strict_types=1);

namespace Mfd\Prometheus\Service;

use Mfd\Prometheus\Event\MetricsCollectingEvent;
use Prometheus\RegistryInterface;
use Prometheus\RenderTextFormat;
use Psr\EventDispatcher\EventDispatcherInterface;

readonly class MetricsCollector
{
    public function __construct(
        private RegistryInterface $registry,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function collectMetrics(): string
    {
        $this->eventDispatcher->dispatch(new MetricsCollectingEvent($this->registry));

        // Render metrics in the Prometheus text format
        // @todo remove braces when PHPStan 2.1 is used
        return (new RenderTextFormat())->render($this->registry->getMetricFamilySamples());
    }
}
