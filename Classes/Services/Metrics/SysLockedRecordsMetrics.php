<?php
namespace Mfc\Prometheus\Services\Metrics;

use Mfc\Prometheus\Domain\Repository\SysLockedRecordsRepository;

class SysLockedRecordsMetrics extends AbstractMetrics
{
    protected $velocity = 'fast';

    public function getVelocity()
    {
        return parent::getVelocity();
    }

    public function getMetricsValues()
    {
        /** @var \Mfc\Prometheus\Domain\Repository\SysLockedRecordsRepository $sysLockedRecordsRepository */
        $sysLockedRecordsRepository = $this->objectManager->get(SysLockedRecordsRepository::class);

        return $this->prepareDataToInsert($sysLockedRecordsRepository->getMetricsValues());
    }
}