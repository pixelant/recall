<?php
declare(strict_types=1);

namespace Pixelant\Recall\Service;

use Pixelant\Recall\Domain\Repository\DataRepository;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

/**
 * Get and set data for recall.
 */
class RecallService
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
     * @param FrontendInterface $cache
     * @param DataRepository $repository
     */
    public function __construct(FrontendInterface $cache, DataRepository $repository)
    {
        $this->cache = $cache;
        $this->repository = $repository;
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

        return unserialize($data);
    }

    /**
     * Persists the data into recall storage.
     *
     * @param array $data
     * @return string The hash used to retrieve the data.
     */
    public function set(array $data): string
    {
        $serializedData = serialize($data);

        $hash = $this->repository->set($serializedData);

        // Ignore cache if it's using database. It won't be any faster.
        if (!$this->cache->getBackend() instanceof Typo3DatabaseBackend) {
            $this->cache->set($hash, $serializedData);
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
