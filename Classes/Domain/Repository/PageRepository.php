<?php
namespace Mfc\Prometheus\Domain\Repository;

class PageRepository extends BaseRepository
{
    public function getMetricsValues()
    {
        $data = [];

        $defaultPages = $this->getDatabaseConnection()->exec_SELECTcountRows(
            'uid',
            'pages',
            '1=1' . $this->getEnableFields('pages')
        );

        $pageOverlays = $this->getDatabaseConnection()->exec_SELECTgetRows(
            'count(uid) as count, sys_language_uid',
            'pages_language_overlay',
            'sys_language_uid > 0' . $this->getEnableFields('pages_language_overlay'),
            'sys_language_uid',
            'sys_language_uid asc',
            '',
            'sys_language_uid'
        );

        if ($defaultPages !== false) {
            $data['typo3_pages_total{sys_language_uid="0"}'] = $defaultPages;
        }


        foreach ($pageOverlays as $singlePageLanguageKey => $singlePageLanguageKeyValues) {
            $data['typo3_pages_total{sys_language_uid="'. $singlePageLanguageKey .'"}'] =
                $singlePageLanguageKeyValues['count'];
        }

        return $data;
    }
}
