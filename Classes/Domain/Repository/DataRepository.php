<?php
declare(strict_types=1);

namespace Pixelant\Recall\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Repository for tx_recall_data.
 */
class DataRepository implements SingletonInterface
{
    public const TABLE_NAME = 'tx_recall_data';

    /**
     * Data cache. Key is the hash.
     *
     * @var array
     */
    protected $dataCache = [];

    /**
     * Get data for hash.
     *
     * @param string $hash
     * @return array|null
     */
    public function get(string $hash): ?array
    {
        if (isset($this->dataCache[$hash])) {
            return $this->dataCache[$hash];
        }

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

        $this->dataCache[$hash] = unserialize($data);

        return $this->dataCache[$hash];
    }

    /**
     * Set data and return hash.
     *
     * @param array $data
     * @return string The data hash.
     */
    public function set(array $data): string
    {
        $serializedData = serialize($data);

        $hash = md5($serializedData);

        if (isset($this->dataCache[$hash]) || $this->exists($hash)) {
            return $hash;
        }

        $queryBuilder = $this->getQueryBuilder();

        $queryBuilder
            ->insert(self::TABLE_NAME)
            ->values([
                'hash' => $hash,
                'data' => $serializedData,
                'tstamp' => time()
            ])
            ->execute();

        $this->dataCache[$hash] = $data;

        return $hash;
    }

    /**
     * Returns true if the hash exists.
     *
     * @param string $hash
     * @return bool
     */
    public function exists(string $hash): bool
    {
        return $this->get($hash) !== null;
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

        unset($this->dataCache[$hash]);
    }

    /**
     * Remove records older than timestamp.
     *
     * @param int $timestamp Unix timestamp of the date before which records can be deleted.
     */
    public function removeOlderThan(int $timestamp): void
    {
        $queryBuilder = $this->getQueryBuilder();

        $queryBuilder
            ->delete(self::TABLE_NAME)
            ->where($queryBuilder->expr()->lt('tstamp', $queryBuilder->createNamedParameter($timestamp)))
            ->execute();

        $this->dataCache = [];
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
