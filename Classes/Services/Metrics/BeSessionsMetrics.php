<?php
namespace Mfc\Prometheus\Services\Metrics;

use Mfc\Prometheus\Domain\Repository\BeSessionsRepository;

class BeSessionsMetrics extends AbstractMetrics
{
    protected $velocity = 'fast';

    public function getVelocity()
    {
        return parent::getVelocity();
    }

    public function getMetricsValues()
    {
        /** @var \Mfc\Prometheus\Domain\Repository\BeSessionsRepository $beSessionRepository */
        $beSessionRepository = $this->objectManager->get(BeSessionsRepository::class);

        return $this->prepareDataToInsert($beSessionRepository->getMetricsValues());
    }
}