<?php
namespace Mfc\Prometheus\Services\Metrics;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

abstract class AbstractMetrics implements MetricsInterface
{

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /** @var string  */
    protected $velocity = 'slow';

    /**
     * AbstractMetrics constructor
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
    }

    /**
     *@return string
     */
    public function getVelocity()
    {
        return $this->velocity;
    }

    /**
     * @return array
     */
    public function getMetricsValues()
    {
        return [];
    }


    /**
     * @param array $data
     * @return array
     */
    protected function prepareDataToInsert($data)
    {
        $output = [];

        foreach ($data as $dataKey => $dataValue) {
            $output[$dataKey] = [$dataKey, $dataValue, $GLOBALS['EXEC_TIME']];
        }

        return $output;
    }
}
