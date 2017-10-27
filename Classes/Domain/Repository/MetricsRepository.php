<?php
/**
 * Created by PhpStorm.
 * User: sfs
 * Date: 26.10.17
 * Time: 16:49
 */

namespace Mfc\Prometheus\Domain\Repository;

class MetricsRepository {


    protected $tablename = 'prometheus_metrics';


    /**
     * @return array
     */
    public function getAllMetrices()
    {
        return $this->getDatabaseConnection()->exec_SELECTgetRows(
            'concat(metric_key, \' \', metric_value) as row',
            $this->tablename,
            '',
            '',
            '',
            '',
            'row'
        );

    }


    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}