<?php
namespace Mfc\Prometheus\Domain\Repository;

class CfCachePagesRepository extends BaseRepository
{
    public function getMetricsValues()
    {
        $data = [];

        $cachedPages = $this->getDatabaseConnection()->exec_SELECTcountRows(
            'id',
            'cf_cache_pages',
            '1=1' . $this->getEnableFields('cf_cache_pages')
        );

        if ($cachedPages !== false) {
            $data['typo3_cf_cache_pages_total'] = $cachedPages;
        }


        return $data;
    }
}
