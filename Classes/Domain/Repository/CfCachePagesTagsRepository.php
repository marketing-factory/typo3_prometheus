<?php
namespace Mfc\Prometheus\Domain\Repository;

class CfCachePagesTagsRepository extends BaseRepository
{
    public function getMetricsValues()
    {
        $data = [];

        $cachedPagesTags = $this->getDatabaseConnection()->exec_SELECTcountRows(
            'id',
            'cf_cache_pages_tags',
            '1=1' . $this->getEnableFields('cf_cache_pages_tags')
        );

        if ($cachedPagesTags !== false) {
            $data['typo3_cf_cache_pages_tags_total'] = $cachedPagesTags;
        }

        $cachedPagesTags = $this->getDatabaseConnection()->exec_SELECTcountRows(
            'distinct(tag)',
            'cf_cache_pages_tags',
            '1=1' . $this->getEnableFields('cf_cache_pages_tags')
        );

        if ($cachedPagesTags !== false) {
            $data['typo3_cf_cache_pages_tags_distinct_total'] = $cachedPagesTags;
        }


        return $data;
    }
}
