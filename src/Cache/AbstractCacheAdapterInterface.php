<?php declare(strict_types=1);

/**
 * Abstract Cache Adapter Interface
 *
 * Extends PSR-16 SimpleCache interface to provide additional cache operations
 * including increment/decrement, statistics, and driver management. Implemented
 * by AbstractCacheAdapter to provide framework cache adapter capabilities.
 *
 * @package  Spin\Cache
 * @author   Spin Framework Team
 * @since    1.0.0
 */

namespace Spin\Cache;

use Psr\SimpleCache\CacheInterface;

interface AbstractCacheAdapterInterface extends CacheInterface
{

  /**
   * Increment a $key's value and return the new value.
   *
   * @param   string      $key      Key name to increment
   * @param   int         $amount   Amount to increment with (default 1)
   *
   * @return  int|bool
   */
  function inc(string $key, int $amount = 1): bool|int;

  /**
   * Decrement a $key's value and return the new value.
   *
   * @param   string      $key      Key name to decrement
   * @param   int         $amount   Amount to decrement with (default 1)
   *
   * @return  int|bool
   */
  function dec(string $key, int $amount = 1): bool|int;

  /**
   * Returns cache statistics
   *
   * @return array
   */
  function statistics(): array;

  /**
   * @return mixed
   */
  function getOptions(): array;

  /**
   * @param mixed $options
   *
   * @return self
   */
  function setOptions(array $options): AbstractCacheAdapterInterface;

  /**
   * Get Driver Name
   * @return string
   */
  function getDriver(): string;

  /**
   * Set Driver Name
   * @param mixed $driver
   * @return self
   */
  function setDriver(string $driver): AbstractCacheAdapterInterface;

  /**
   * Get Driver Version
   * @return mixed
   */
  function getVersion(): string;

  /**
   * Set Driver Version
   * @param mixed $version
   * @return self
   */
  function setVersion(string $version): AbstractCacheAdapterInterface;
}
