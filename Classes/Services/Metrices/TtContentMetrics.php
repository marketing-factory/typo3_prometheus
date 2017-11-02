<?php
namespace Mfc\Prometheus\Services\Metrices;

use Mfc\Prometheus\Domain\Repository\TtContentRepository;

class TtContentMetrics extends AbstractMetrics
{
    protected $velocity = 'fast';

    public function getVelocity()
    {
        return parent::getVelocity();
    }

    public function getMetricsValues()
    {
        /** @var \Mfc\Prometheus\Domain\Repository\TtContentRepository $ttContentRepository */
        $ttContentRepository = $this->objectManager->get(TtContentRepository::class);

        return $this->prepareDataToInsert($ttContentRepository->getMetricsValues());
    }
}