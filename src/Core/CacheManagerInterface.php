<?php declare(strict_types=1);

/**
 * CacheManagerInterface
 *
 * @package   Spin
 */

namespace Spin\Core;

use \Spin\Cache\AbstractCacheAdapter;
use \Spin\Cache\AbstractCacheAdapterInterface;

interface CacheManagerInterface
{
  /**
   * Get or Create a Cache
   *
   * @param      string  $name   Name of the Cache (from Config)
   * @return     null  | AbstractCacheAdapter
   */
  public function getCache(string $name='');

  /**
   * Find a Cache based on name
   *
   * If the $name is empty/null we'll return the 1st cache in the internal list
   * (if there is one)
   *
   * @param      string  $name   Name of the cache (from Config)
   *
   * @return     null  | AbstractCacheAdapter
   */
  public function findCache(string $name='');

  /**
   * Adds the Cache to the Pool
   *
   * @param      AbstractCacheAdapterInterface  $cache  [description]
   *
   * @return     self
   */
  public function addCache(AbstractCacheAdapterInterface $cache);

  /**
   * Remove a cache from the pool
   *
   * @param      string  $name   The name
   *
   * @return     self
   */
  public function removeCache(string $name);

  /**
   * Get array of containers
   *
   * @return     array
   */
  public function getCaches(): array;
}
