<?php

namespace Mfd\Prometheus\EventListener\Metrics;

use Doctrine\DBAL\Exception\InvalidFieldNameException;
use Mfd\Prometheus\Event\MetricsCollectingEvent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;

readonly class Database
{
    private const int TABLE_COUNT_TTL = 300;
    private const int TABLE_NAMES_TTL = 300;

    public function __construct(
        #[Autowire(service: 'cache.prometheus')]
        private FrontendInterface $cache,
        private ConnectionPool $connectionPool
    ) {
    }

    public function __invoke(MetricsCollectingEvent $event): void
    {
        $tables = $this->getAllTables();

        $rowGauge = $event->getRegistry()->getOrRegisterGauge(
            'typo3',
            'database_rows',
            'Number of rows by database table',
            ['table']
        );
        $deletedGauge = $event->getRegistry()->getOrRegisterGauge(
            'typo3',
            'database_rows_deleted',
            'Number of deleted rows by database table',
            ['table']
        );

        foreach ($tables as $tableName) {
            $rowGauge->set($this->fetchTableRawCount($tableName), ['table' => $tableName]);
            $deletedGauge->set($this->fetchTableDeletedCount($tableName), ['table' => $tableName]);
        }
    }

    protected function fetchTableRawCount(string $tableName): int
    {
        $cacheKey = 'table_count_' . $tableName . '-raw';
        $value = $this->cache->get($cacheKey);

        if ($value === false) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable($tableName);
            $queryBuilder->getRestrictions()->removeAll();

            $count = $queryBuilder
                ->count('*')
                ->from($tableName)
                ->executeQuery()
                ->fetchOne();

            $value = (int)$count;
            $this->cache->set($cacheKey, $value, ["table_count"], self::TABLE_COUNT_TTL);
        }

        return $value;
    }

    protected function fetchTableDeletedCount(string $tableName): int
    {
        $cacheKey = 'table_count_' . $tableName . '-deleted';
        $value = $this->cache->get($cacheKey);

        if ($value === false) {
            try {
                $queryBuilder = $this->connectionPool->getQueryBuilderForTable($tableName);
                $queryBuilder->getRestrictions()->removeAll();

                $count = $queryBuilder
                    ->count('*')
                    ->from($tableName)
                    ->where($queryBuilder->expr()->eq('deleted', 1))
                    ->executeQuery()
                    ->fetchOne();

                $value = (int)$count;
                $this->cache->set($cacheKey, $value, ["table_count"], self::TABLE_COUNT_TTL);
            } catch (InvalidFieldNameException) {
                // Table does not have a deleted column
                $value = 0;
                $this->cache->set($cacheKey, $value, ["table_count"]);
            }
        }

        return $value;
    }

    private function getAllTables(): array
    {
        $cacheKey = 'table_names';
        $value = $this->cache->get($cacheKey);

        if ($value === false) {
            $schemaManager = $this->connectionPool->getConnectionForTable('pages')->createSchemaManager();
            $value = $schemaManager->listTableNames();

            $this->cache->set($cacheKey, $value, ["table_names"], self::TABLE_NAMES_TTL);
        }

        return $value;
    }
}
