<?php
namespace Mfc\Prometheus\Services\Metrics;

use Mfc\Prometheus\Domain\Repository\SysDomainRepository;

class SysDomainMetrics extends AbstractMetrics
{
    protected $velocity = 'fast';

    public function getVelocity()
    {
        return parent::getVelocity();
    }

    public function getMetricsValues()
    {
        /** @var \Mfc\Prometheus\Domain\Repository\SysDomainRepository $sysDomainRepository */
        $sysDomainRepository = $this->objectManager->get(SysDomainRepository::class);

        return $this->prepareDataToInsert($sysDomainRepository->getMetricsValues());
    }
}