<?php
namespace Mfc\Prometheus\Domain\Repository;

class SysLogRepository extends BaseRepository
{
    public function getMetricsValues()
    {
        $data = [];

        $successfullLogins = $this->getDatabaseConnection()->exec_SELECTcountRows(
            'uid',
            'sys_log',
            'type=255 and error=0' . $this->getEnableFields('sys_log')
        );

        if ($successfullLogins !== false) {
            $data['typo3_sys_log_successfull_logins_total'] = $successfullLogins;
        }

        $failedLogins = $this->getDatabaseConnection()->exec_SELECTcountRows(
            'uid',
            'sys_log',
            'type=255 and error=3' . $this->getEnableFields('sys_log')
        );

        if ($failedLogins !== false) {
            $data['typo3_sys_log_failed_logins_total'] = $failedLogins;
        }

        $captchafailedLogins = $this->getDatabaseConnection()->exec_SELECTcountRows(
            'uid',
            'sys_log',
            'type=255 and error=3 and details_nr =3' . $this->getEnableFields('sys_log')
        );

        if ($captchafailedLogins !== false) {
            $data['typo3_sys_log_captcha_failed_logins_total'] = $captchafailedLogins;
        }


        $clearCaches = $this->getDatabaseConnection()->exec_SELECTcountRows(
            'uid',
            'sys_log',
            'type=3' . $this->getEnableFields('sys_log')
        );

        if ($clearCaches !== false) {
            $data['typo3_sys_log_cleared_caches_total'] = $clearCaches;
        }


        $insertedRecords = $this->getDatabaseConnection()->exec_SELECTgetRows(
            'count(uid) as count,tablename',
            'sys_log',
            'type = 1  and action = 1' . $this->getEnableFields('sys_log'),
            'tablename',
            'tablename asc'
        );


        foreach ($insertedRecords as $singleContentTypes) {
            $key = 'typo3_sys_log_inserted_records_total{tablename="'. $singleContentTypes['tablename'] .'"}';
            $data[$key] = $singleContentTypes['count'];
        }


        $deletedRecords = $this->getDatabaseConnection()->exec_SELECTgetRows(
            'count(uid) as count,tablename',
            'sys_log',
            'type = 1 and action = 3' . $this->getEnableFields('sys_log'),
            'tablename',
            'tablename asc'
        );


        foreach ($deletedRecords as $singleContentTypes) {
            $key = 'typo3_sys_log_deleted_records_total{tablename="'. $singleContentTypes['tablename'] .'"}';
            $data[$key] = $singleContentTypes['count'];
        }

        $updatedRecords = $this->getDatabaseConnection()->exec_SELECTgetRows(
            'count(uid) as count,tablename',
            'sys_log',
            'type = 1 and action = 2' . $this->getEnableFields('sys_log'),
            'tablename',
            'tablename asc'
        );


        foreach ($updatedRecords as $singleContentTypes) {
            $key = 'typo3_sys_log_updated_records_total{tablename="'. $singleContentTypes['tablename'] .'"}';
            $data[$key] = $singleContentTypes['count'];
        }

        return $data;
    }
}
