<?php
namespace Mfc\Prometheus\Domain\Repository;

class FeSessionsRepository extends BaseRepository
{
    public function getMetricsValues()
    {
        $data = [];

        $cachedPages = $this->getDatabaseConnection()->exec_SELECTcountRows(
            'ses_id',
            'fe_sessions',
            '1=1' . $this->getEnableFields('fe_sessions')
        );

        if ($cachedPages !== false) {
            $data['typo3_fe_sessions_total'] = $cachedPages;
        }


        return $data;
    }
}
