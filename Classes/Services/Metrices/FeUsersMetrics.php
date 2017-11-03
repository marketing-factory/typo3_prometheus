<?php
namespace Mfc\Prometheus\Services\Metrices;

use Mfc\Prometheus\Domain\Repository\FeUsersRepository;

class FeUsersMetrics extends AbstractMetrics
{
    protected $velocity = 'fast';

    public function getVelocity()
    {
        return parent::getVelocity();
    }

    public function getMetricsValues()
    {
        /** @var \Mfc\Prometheus\Domain\Repository\FeUsersRepository $feUsersRepository */
        $feUsersRepository = $this->objectManager->get(FeUsersRepository::class);

        return $this->prepareDataToInsert($feUsersRepository->getMetricsValues());
    }
}