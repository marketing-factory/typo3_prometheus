<?php

namespace Mfd\Prometheus\Event;

use Prometheus\RegistryInterface;

readonly class MetricsCollectingEvent
{
    public function __construct(private RegistryInterface $registry)
    {
    }

    public function getRegistry(): RegistryInterface
    {
        return $this->registry;
    }
}
