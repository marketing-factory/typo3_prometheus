<?php
namespace Mfc\Prometheus\Domain\Repository;

class BeUsersRepository extends BaseRepository
{
    public function getMetricsValues()
    {
        $data = [];

        $cachedPages = $this->getDatabaseConnection()->exec_SELECTcountRows(
            'uid',
            'be_users',
            '1=1' . $this->getEnableFields('be_users')
        );

        if ($cachedPages !== false) {
            $data['typo3_be_users_total'] = $cachedPages;
        }


        return $data;
    }
}
