<?php
namespace Mfc\Prometheus\Services\Metrices;

use Mfc\Prometheus\Domain\Repository\CfCachePagesRepository;

class CfCachePagesMetrics extends AbstractMetrics
{
    protected $velocity = 'fast';

    public function getVelocity()
    {
        return parent::getVelocity();
    }

    public function getMetricsValues()
    {
        /** @var \Mfc\Prometheus\Domain\Repository\CfCachePagesRepository $cfCachePagesRepository */
        $cfCachePagesRepository = $this->objectManager->get(CfCachePagesRepository::class);

        return $this->prepareDataToInsert($cfCachePagesRepository->getMetricsValues());
    }
}