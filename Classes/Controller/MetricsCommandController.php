<?php

namespace Mfc\Prometheus\Controller;

use Mfc\Prometheus\Domain\Repository\MetricsRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MetricsCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController
{
    protected  $metricesToWork = [];

    /** @var MetricsRepository  */
    protected $metricsRepository;

    public function __construct()
    {
        $this->metricsRepository = GeneralUtility::makeInstance(MetricsRepository::class);
    }

    public function generateAllFastMetricesCommand()
    {
        $this->initializeMetrices('fast');

        $this->getValuesAndWriteToDb();

    }

    public function generateAllSlowMetricesCommand()
    {
        $this->initializeMetrices('slow');
        $this->getValuesAndWriteToDb();

    }

    public function generateAllMediumMetricesCommand()
    {
        $this->initializeMetrices('medium');
        $this->getValuesAndWriteToDb();

    }

    protected function initializeMetrices($velocity = '')
    {
        foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['prometheus']['metricesToMeasure'] as $singleMetrics) {
            $metricsHelper = GeneralUtility::makeInstance($singleMetrics);
            if ($velocity != '' && $metricsHelper->getVelocity()) {
                $this->metricesToWork[] = $metricsHelper;
            }
        }

    }

    protected function getValuesAndWriteToDb()
    {

        foreach ($this->metricesToWork as $singleMetrics) {
            $dataToInsert = $singleMetrics->getMetricsValues();
            if (!empty($dataToInsert)) {
                $this->metricsRepository->saveDataToDb($dataToInsert);
            }
        }

    }
}