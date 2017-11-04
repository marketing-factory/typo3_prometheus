<?php
namespace Mfc\Prometheus\Services\Metrics;

use Mfc\Prometheus\Domain\Repository\SysLogRepository;

class SysLogMetrics extends AbstractMetrics
{
    protected $velocity = 'slow';

    public function getVelocity()
    {
        return parent::getVelocity();
    }

    public function getMetricsValues()
    {
        /** @var \Mfc\Prometheus\Domain\Repository\SysLogRepository $sysLogRepository */
        $sysLogRepository = $this->objectManager->get(SysLogRepository::class);

        return $this->prepareDataToInsert($sysLogRepository->getMetricsValues());
    }
}