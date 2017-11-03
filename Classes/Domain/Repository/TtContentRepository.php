<?php
namespace Mfc\Prometheus\Domain\Repository;

class TtContentRepository extends BaseRepository
{
    public function getMetricsValues()
    {
        $data = [];

        $contentTypesByLanguage = $this->getDatabaseConnection()->exec_SELECTgetRows(
            'count(uid) as count, sys_language_uid, cType',
            'tt_content',
            'sys_language_uid >=0' . $this->getEnableFields('tt_content'),
            'sys_language_uid, cType',
            'sys_language_uid asc'
        );


        foreach ($contentTypesByLanguage as $singleContentTypes) {
            $key = 'typo3_tt_content_total{sys_language_uid="'. $singleContentTypes['sys_language_uid'] .'", cType="'.
                $singleContentTypes['cType'].'"}';
            $data[$key] =
                $singleContentTypes['count'];

            $languageKey = 'typo3_tt_content_total{sys_language_uid="'. $singleContentTypes['sys_language_uid'] .'"}';
            $typeKey = 'typo3_tt_content_total{cType="'. $singleContentTypes['cType'] .'"}';

            $data[$languageKey] = $data[$languageKey] + $singleContentTypes['count'];
            $data[$typeKey] = $data[$typeKey] + $singleContentTypes['count'];
            $data['typo3_tt_content_total'] = $data['typo3_tt_content_total'] + $singleContentTypes['count'];

        }

        return $data;
    }
}
