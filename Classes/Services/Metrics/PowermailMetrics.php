<?php
namespace Mfc\Prometheus\Services\Metrics;

use Mfc\Prometheus\Domain\Repository\PowermailRepository;

/**
 * Class PowermailMetrics
 * @package Mfc\Prometheus\Services\Metrics
 */
class PowermailMetrics extends AbstractMetrics
{
    protected $velocity = 'fast';

    /**
     * @return array
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function getMetricsValues()
    {
        /** @var \Mfc\Prometheus\Domain\Repository\PowermailRepository $pageRepository */
        $pageRepository = $this->objectManager->get(PowermailRepository::class);

        return $this->prepareDataToInsert($pageRepository->getMetricsValues());
    }
}