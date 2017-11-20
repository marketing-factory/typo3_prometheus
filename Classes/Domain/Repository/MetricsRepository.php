<?php
/**
 * Created by PhpStorm.
 * User: sfs
 * Date: 26.10.17
 * Time: 16:49
 */

namespace Mfc\Prometheus\Domain\Repository;


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
        $query = $this->getDatabaseConnection()->INSERTmultipleRows(
            $this->tablename,
            ['metric_key', 'metric_value', 'tstamp'],
            $data
        );

        $query = preg_replace('@INSERT INTO@', 'REPLACE INTO', $query);

        $this->getDatabaseConnection()->sql_query(
            $query
        );

    }
}
