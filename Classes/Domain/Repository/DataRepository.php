<?php
declare(strict_types=1);

namespace Pixelant\Recall\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Repository for tx_recall_data.
 */
class DataRepository
{
    public const TABLE_NAME = 'tx_recall_data';

    /**
     * Get data for hash.
     *
     * @param string $hash
     * @return string|null
     */
    public function get(string $hash): ?string
    {
        $queryBuilder = $this->getQueryBuilder();

        $data = $queryBuilder
            ->select('data')
            ->from(self::TABLE_NAME)
            ->where($queryBuilder->expr()->eq('hash', $queryBuilder->createNamedParameter($hash)))
            ->execute()
            ->fetchOne();

        if ($data === false) {
            return null;
        }

        return $data;
    }

    /**
     * Set data and return hash.
     *
     * @param string $data
     * @return string The data hash.
     */
    public function set(string $data): string
    {
        $hash = md5($data);

        $queryBuilder = $this->getQueryBuilder();

        $queryBuilder
            ->insert(self::TABLE_NAME)
            ->values([
                'hash' => $hash,
                'data' => $data,
                'tstamp' => time()
            ])
            ->execute();

        return $hash;
    }

    /**
     * Remove data based on hash.
     *
     * @param string $hash
     */
    public function remove(string $hash): void
    {
        $queryBuilder = $this->getQueryBuilder();

        $queryBuilder
            ->delete(self::TABLE_NAME)
            ->where($queryBuilder->expr()->eq('hash', $queryBuilder->createNamedParameter($hash)))
            ->execute();
    }

    /**
     * Remove records older than timestamp.
     *
     * @param int $timestamp
     */
    public function removeOlderThan(int $timestamp): void
    {
        $queryBuilder = $this->getQueryBuilder();

        $queryBuilder
            ->delete(self::TABLE_NAME)
            ->where($queryBuilder->expr()->lt('tstamp', $queryBuilder->createNamedParameter($timestamp)))
            ->execute();
    }

    /**
     * Update the access timestamp for the hash.
     *
     * @param string $hash
     */
    public function updateTimestamp(string $hash): void
    {
        $queryBuilder = $this->getQueryBuilder();

        $queryBuilder
            ->update(self::TABLE_NAME)
            ->where($queryBuilder->expr()->eq('hash', $queryBuilder->createNamedParameter($hash)))
            ->set('tstamp', time())
            ->execute();
    }

    /**
     * Get the query builder.
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TABLE_NAME);
    }
}
