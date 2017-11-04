<?php
namespace Mfc\Prometheus\Domain\Repository;

class SysLockedRecordsRepository extends BaseRepository
{
    public function getMetricsValues()
    {
        $data = [];


        $lockedRecords = $this->getDatabaseConnection()->exec_SELECTgetRows(
            'count(uid) as count,record_table',
            'sys_lockedrecords',
            '1=1' . $this->getEnableFields('sys_lockedrecords'),
            'record_table',
            'record_table asc'
        );

        foreach ($lockedRecords as $singleContentTypes) {
            $key = 'typo3_sys_locked_records_total{record_table="'. $singleContentTypes['record_table'] .'"}';
            $data[$key] = $singleContentTypes['count'];
        }


        return $data;
    }
}
