<?php
namespace Mfc\Prometheus\Services\Metrics;

use Mfc\Prometheus\Domain\Repository\BeUsersRepository;

class BeUsersMetrics extends AbstractMetrics
{
    protected $velocity = 'fast';

    public function getVelocity()
    {
        return parent::getVelocity();
    }

    public function getMetricsValues()
    {
        /** @var \Mfc\Prometheus\Domain\Repository\BeUsersRepository $beUsersRepository */
        $beUsersRepository = $this->objectManager->get(BeUsersRepository::class);

        return $this->prepareDataToInsert($beUsersRepository->getMetricsValues());
    }
}