<?php declare(strict_types=1);

namespace Spin\Core;

use \Spin\Cache\AbstractCacheAdapter;
use \Spin\Cache\AbstractCacheAdapterInterface;

interface CacheManagerInterface
{
  /**
   * Get or Create a Cache
   *
   * @param  string $name         Name of the Cache (from Config)
   * @return null | object
   */
  public function getCache(string $name=null);

  /**
   * Find a Cache based on name
   *
   * If the $name is empty/null we'll return the 1st
   * cache in the internal list (if there is one)
   *
   * @param  string   $name       Name of the cache (from Config)
   * @return null | PdoConnection
   */
  public function findCache(string $name=null);

  /**
   * Adds the Cache to the Pool
   *
   * @param [type] $cache [description]
   * @return  connection
   */
  public function addCache(AbstractCacheAdapterInterface $cache);

  /**
   * Remove a cache from the pool
   *
   * @param  [type] $cache Name of cache to remove
   * @return bool
   */
  public function removeCache(string $name);

  /**
   * Get array of containers
   *
   * @return array
   */
  public function getCaches(): array;
}
