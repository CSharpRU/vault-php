<?php


namespace Vault;

use Cache\Adapter\Common\CacheItem;

class CachedClient extends Client
{
    const READ_CACHE_KEY = 'vault_client_read_cache';

    /**
     * @var bool
     */
    protected $readCacheEnabled = false;

    /**
     * @var int
     */
    protected $readCacheTtl = 3600;

    /**
     * @inheritdoc
     *
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function read($path)
    {
        if (!$this->readCacheEnabled) {
            return parent::read($path);
        }

        if (!$this->cache) {
            $this->logger->warning('Cache is enabled, but cache pool is not set.');

            return parent::read($path);
        }

        $key = self::READ_CACHE_KEY . str_replace(['{', '}', '(', ')', '/', '\\', '@', '-'], '_', $path);

        if ($this->cache->hasItem($key)) {
            $this->logger->debug('Has read response in cache.', ['path' => $path]);

            return $this->cache->getItem($key)->get();
        }

        $response = parent::read($path);

        $item = (new CacheItem($key))->set($response)->expiresAfter($this->readCacheTtl);

        $this->logger->debug('Saving read response in cache.', ['path' => $path, 'item' => $item]);

        if (!$this->cache->save($item)) {
            $this->logger->warning('Cannot save read response into cache.', ['path' => $path]);
        }

        return $response;
    }

    /**
     * @return bool
     */
    public function isReadCacheEnabled()
    {
        return $this->readCacheEnabled;
    }

    /**
     * @return $this
     */
    public function enableReadCache()
    {
        $this->readCacheEnabled = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function disableReadCache()
    {
        $this->readCacheEnabled = false;

        return $this;
    }

    /**
     * @return int
     */
    public function getReadCacheTtl()
    {
        return $this->readCacheTtl;
    }

    /**
     * @param int $readCacheTtl
     *
     * @return $this
     */
    public function setReadCacheTtl($readCacheTtl)
    {
        $this->readCacheTtl = $readCacheTtl;

        return $this;
    }
}
