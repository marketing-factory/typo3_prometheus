<?php
namespace Mfc\Prometheus\Services\Metrics;

/**
 * Interface MetricsInterface
 */
interface MetricsInterface
{
    /**
     *
     * @return string
     */
    public function getVelocity();

    /**
     *
     * @return array
     */
    public function getMetricsValues();
}
