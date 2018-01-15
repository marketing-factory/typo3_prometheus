<?php
/**
 * Created by PhpStorm.
 * User: sfs
 * Date: 26.10.17
 * Time: 16:49
 */

namespace Mfc\Prometheus\Domain\Repository;


use TYPO3\CMS\Core\Utility\GeneralUtility;

class MetricsRepository extends BaseRepository
{


    protected $tablename = 'prometheus_metrics';


    /**
     * @return array
     */
    public function getAllMetrics()
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
     * @param array $data
     * @return void
     */
    public function saveDataToDb($data)
    {
        $this->getDatabaseConnection()->exec_INSERTmultipleRows(
            $this->tablename,
            ['metric_key', 'metric_value', 'tstamp'],
            $data
        );

    }

    public function deleteOldMetricData($keys)
    {
        $this->getDatabaseConnection()->exec_DELETEquery(
            $this->tablename,
        'metric_key in (' . implode(',', $this->getDatabaseConnection()->fullQuoteArray($keys, $this->tablename)) .')'
        );
    }
}
