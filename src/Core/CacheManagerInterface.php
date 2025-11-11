<?php declare(strict_types=1);

/**
 * Cache Manager Interface
 *
 * Defines the contract for cache management operations including cache
 * resolution, creation, pooling, and lifecycle management. Implemented
 * by CacheManager to provide centralized cache administration.
 *
 * @package   Spin\Core
 * @author    Spin Framework Team
 * @since     1.0.0
 */

namespace Spin\Core;

use \Spin\Cache\AbstractCacheAdapter;
use \Spin\Cache\AbstractCacheAdapterInterface;

/**
 * Contract for a cache manager capable of resolving cache adapters by name,
 * creating them on demand, and exposing the set of managed cache instances.
 */
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
