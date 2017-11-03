<?php
namespace Mfc\Prometheus\Services\Metrics;

use Mfc\Prometheus\Domain\Repository\FeSessionsRepository;

class FeSessionsMetrics extends AbstractMetrics
{
    protected $velocity = 'fast';

    public function getVelocity()
    {
        return parent::getVelocity();
    }

    public function getMetricsValues()
    {
        /** @var \Mfc\Prometheus\Domain\Repository\FeSessionsRepository $feSessionsRepository */
        $feSessionsRepository = $this->objectManager->get(FeSessionsRepository::class);

        return $this->prepareDataToInsert($feSessionsRepository->getMetricsValues());
    }
}