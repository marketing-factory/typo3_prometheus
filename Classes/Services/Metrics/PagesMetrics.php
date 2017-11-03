<?php
namespace Mfc\Prometheus\Services\Metrics;

use Mfc\Prometheus\Domain\Repository\PageRepository;

class PagesMetrics extends AbstractMetrics
{
    protected $velocity = 'slow';

    public function getVelocity()
    {
        return parent::getVelocity();
    }

    public function getMetricsValues()
    {
        /** @var \Mfc\Prometheus\Domain\Repository\PageRepository $pageRepository */
        $pageRepository = $this->objectManager->get(PageRepository::class);

        return $this->prepareDataToInsert($pageRepository->getMetricsValues());
    }
}