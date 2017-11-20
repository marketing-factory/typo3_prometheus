<?php

namespace Mfc\Prometheus\Controller;

use Mfc\Prometheus\Domain\Repository\MetricsRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MetricsCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController
{
    protected  $metricsToWork = [];

    /** @var MetricsRepository  */
    protected $metricsRepository;

    public function __construct()
    {
        $this->metricsRepository = GeneralUtility::makeInstance(MetricsRepository::class);
    }

    public function generateAllFastMetricsCommand()
    {
        $this->initializeMetrics('fast');

        $this->getValuesAndWriteToDb();

    }

    public function generateAllSlowMetricsCommand()
    {
        $this->initializeMetrics('slow');
        $this->getValuesAndWriteToDb();

    }

    public function generateAllMediumMetricsCommand()
    {
        $this->initializeMetrics('medium');
        $this->getValuesAndWriteToDb();

    }

    protected function initializeMetrics($velocity = '')
    {
        foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['prometheus']['metricsToMeasure'][$velocity] as $singleMetrics) {
            $metricsHelper = GeneralUtility::makeInstance($singleMetrics);
            if ($velocity != '' && $metricsHelper->getVelocity()) {
                $this->metricsToWork[] = $metricsHelper;
            }
        }

    }

    protected function getValuesAndWriteToDb()
    {

        foreach ($this->metricsToWork as $singleMetrics) {
            $dataToInsert = $singleMetrics->getMetricsValues();
            if (!empty($dataToInsert)) {
                $this->metricsRepository->saveDataToDb($dataToInsert);
            }
        }

    }
}