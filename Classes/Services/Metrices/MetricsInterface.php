<?php
namespace Mfc\Prometheus\Services\Metrices;

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
