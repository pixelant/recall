<?php
declare(strict_types=1);

namespace Pixelant\Recall\Service;

use Pixelant\Recall\Domain\Repository\DataRepository;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Get and set data for recall.
 */
class RecallService implements SingletonInterface
{
    /**
     * @var FrontendInterface
     */
    protected $cache;

    /**
     * @var DataRepository
     */
    protected $repository;

    /**
     * RecallService constructor.
     *
     * @param DataRepository $repository
     * @param FrontendInterface $cache
     */
    public function __construct(DataRepository $repository = null, FrontendInterface $cache = null)
    {
        $this->cache = $cache ?? GeneralUtility::makeInstance(CacheManager::class)->getCache('recall_data');
        $this->repository = $repository ?? GeneralUtility::makeInstance(DataRepository::class);
    }

    /**
     * Returns the array data for the given hash.
     *
     * @param string $hash
     * @return array|null
     */
    public function get(string $hash): ?array
    {
        $data = $this->getFromCache($hash) ?? $this->repository->get($hash);

        if ($data === null) {
            return null;
        }

        $this->repository->updateTimestamp($hash);

        return $data;
    }

    /**
     * Persists the data into recall storage.
     *
     * @param array $data
     * @return string The hash used to retrieve the data.
     */
    public function set(array $data): string
    {
        $hash = $this->repository->set($data);

        // Ignore cache if it's using database. It won't be any faster.
        if (!$this->cache->getBackend() instanceof Typo3DatabaseBackend) {
            $this->cache->set($hash, $this->repository->get($hash));
        }

        return $hash;
    }

    /**
     * Get data from cache if it is persisted there.
     *
     * @param string $hash
     * @return string|null
     */
    protected function getFromCache(string $hash): ?string
    {
        // Ignore cache if it's using database. It won't be any faster.
        if ($this->cache->getBackend() instanceof Typo3DatabaseBackend) {
            return null;
        }

        return $this->cache->get($hash);
    }
}
