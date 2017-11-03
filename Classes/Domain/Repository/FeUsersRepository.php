<?php
namespace Mfc\Prometheus\Domain\Repository;

class FeUsersRepository extends BaseRepository
{
    public function getMetricsValues()
    {
        $data = [];

        $users = $this->getDatabaseConnection()->exec_SELECTcountRows(
            'uid',
            'fe_users',
            '1=1' . $this->getEnableFields('fe_users')
        );

        if ($users !== false) {
            $data['typo3_fe_users_total'] = $users;
        }


        return $data;
    }
}
