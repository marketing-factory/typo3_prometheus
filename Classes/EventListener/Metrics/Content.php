<?php

namespace Mfd\Prometheus\EventListener\Metrics;

use Mfd\Prometheus\Event\MetricsCollectingEvent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\DocumentTypeExclusionRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;

#[AsEventListener]
class Content
{
    private const int COUNT_TTL = 300;

    public function __construct(
        #[Autowire(service: 'cache.prometheus')]
        private readonly FrontendInterface $cache,
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    public function __invoke(MetricsCollectingEvent $event): void
    {
        // Fetch total page count
        $pageCount = $this->fetchPageCount(false);
        $gauge = $event->getRegistry()->getOrRegisterGauge(
            'typo3',
            'pages_total',
            'Total number of all pages (including deleted pages)'
        );
        $gauge->set($pageCount);

        // Fetch visible page count
        $visiblePageCount = $this->fetchPageCount();
        $gauge = $event->getRegistry()->getOrRegisterGauge(
            'typo3',
            'pages_visible',
            'Total number of visible pages'
        );
        $gauge->set($visiblePageCount);

        // Fetch content element count
        $contentCount = $this->fetchTableCount('tt_content', false);
        $gauge = $event->getRegistry()->getOrRegisterGauge(
            'typo3',
            'content_elements_total',
            'Total number of content elements'
        );
        $gauge->set($contentCount);

        // Fetch current content element count
        $contentCount = $this->fetchTableCount('tt_content');
        $gauge = $event->getRegistry()->getOrRegisterGauge(
            'typo3',
            'content_elements_current',
            'Total number of currently active content elements'
        );
        $gauge->set($contentCount);
    }

    protected function fetchTableCount(string $tableName, bool $onlyVisible = true): int
    {
        $cacheKey = 'table_count_' . $tableName . ($onlyVisible ? '-only_visible' : '');
        $value = $this->cache->get($cacheKey);

        if ($value === false || !is_numeric($value)) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable($tableName);


            if (!$onlyVisible) {
                $queryBuilder->getRestrictions()->removeByType(StartTimeRestriction::class);
                $queryBuilder->getRestrictions()->removeByType(EndTimeRestriction::class);
                $queryBuilder->getRestrictions()->removeByType(DeletedRestriction::class);
                $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
            }

            $queryBuilder->getRestrictions()->add(new WorkspaceRestriction());

            $count = $queryBuilder
                ->count('*')
                ->from($tableName)
                ->executeQuery()
                ->fetchOne();

            $value = (int)$count;
            $this->cache->set($cacheKey, $value, ["table_count"], self::COUNT_TTL);
        }

        return $value;
    }

    private function fetchPageCount(bool $onlyVisible = true): int
    {
        $cacheKey = 'page_count' . ($onlyVisible ? '-only_visible' : '');
        $value = $this->cache->get($cacheKey);

        if ($value === false || !is_numeric($value)) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');

            if (!$onlyVisible) {
                $queryBuilder->getRestrictions()->removeByType(StartTimeRestriction::class);
                $queryBuilder->getRestrictions()->removeByType(EndTimeRestriction::class);
                $queryBuilder->getRestrictions()->removeByType(DeletedRestriction::class);
                $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
            }

            $queryBuilder->getRestrictions()->add(new WorkspaceRestriction());
            $queryBuilder->getRestrictions()->add(new DocumentTypeExclusionRestriction([
                PageRepository::DOKTYPE_LINK,
                PageRepository::DOKTYPE_SHORTCUT,
                PageRepository::DOKTYPE_SPACER,
                PageRepository::DOKTYPE_SYSFOLDER,
            ]));

            $count = $queryBuilder
                ->count('*')
                ->from('pages')
                ->where(
                    $queryBuilder->expr()->eq('sys_language_uid', 0)  // Default language
                )
                ->executeQuery()
                ->fetchOne();

            $value = (int)$count;
            $this->cache->set($cacheKey, $value, ['page_count'], self::COUNT_TTL);
        }

        return $value;
    }
}
