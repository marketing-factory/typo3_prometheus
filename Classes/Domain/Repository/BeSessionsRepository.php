<?php
namespace Mfc\Prometheus\Domain\Repository;

class BeSessionsRepository extends BaseRepository
{
    public function getMetricsValues()
    {
        $data = [];

        $cachedPages = $this->getDatabaseConnection()->exec_SELECTcountRows(
            'ses_id',
            'be_sessions',
            '1=1' . $this->getEnableFields('be_sessions')
        );

        if ($cachedPages !== false) {
            $data['typo3_be_sessions_total'] = $cachedPages;
        }


        return $data;
    }
}
