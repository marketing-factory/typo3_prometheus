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


        return $data;
    }
}
