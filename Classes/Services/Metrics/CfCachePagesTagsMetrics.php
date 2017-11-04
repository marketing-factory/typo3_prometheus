<?php
namespace Mfc\Prometheus\Services\Metrics;

use Mfc\Prometheus\Domain\Repository\CfCachePagesTagsRepository;

class CfCachePagesTagsMetrics extends AbstractMetrics
{
    protected $velocity = 'fast';

    public function getVelocity()
    {
        return parent::getVelocity();
    }

    public function getMetricsValues()
    {
        /** @var \Mfc\Prometheus\Domain\Repository\CfCachePagesTagsRepository $cfCachePagesTagsRepository */
        $cfCachePagesTagsRepository = $this->objectManager->get(CfCachePagesTagsRepository::class);

        return $this->prepareDataToInsert($cfCachePagesTagsRepository->getMetricsValues());
    }
}