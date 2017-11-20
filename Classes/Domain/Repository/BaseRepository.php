<?php
namespace Mfc\Prometheus\Domain\Repository;

use TYPO3\CMS\Backend\Utility\BackendUtility;

class BaseRepository
{


    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    protected function getEnableFields($table)
    {
        return BackendUtility::BEenableFields($table) . BackendUtility::deleteClause($table);
    }
}