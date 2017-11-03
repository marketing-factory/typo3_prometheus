<?php
namespace Mfc\Prometheus\Domain\Repository;

class SysDomainRepository extends BaseRepository
{
    public function getMetricsValues()
    {
        $data = [];

        $sysDomains = $this->getDatabaseConnection()->exec_SELECTcountRows(
            'uid',
            'sys_domain',
            '1=1' . $this->getEnableFields('sys_domain')
        );

        if ($sysDomains !== false) {
            $data['typo3_sys_domain_total'] = $sysDomains;
        }


        return $data;
    }
}
