<?php declare(strict_types=1);

namespace Spin\Cache;

use Psr\SimpleCache\CacheInterface;

interface AbstractCacheAdapterInterface extends CacheInterface
{
  // /**
  //  * Get $key from cache
  //  *
  //  * @param   string $key             Name of KEY
  //  * @param   mixed $default          Optional. Value to return if Key is missing
  //  *
  //  * @return  mixed
  //  */
  // function get(string $key, mixed $default = null): mixed;

  // /**
  //  * Set the $key to $value, with $ttl (default 0)
  //  *
  //  * @param   mixed $key              Name of KEY
  //  * @param   mixed $value            Value to set
  //  * @param   mixed $ttl              TimeToLive in seconds. 0=Infinite
  //  *
  //  * @return  bool
  //  */
  // function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool;

  // /**
  //  * Delete the $key
  //  *
  //  * @param   string $key             Name of KEY
  //  *
  //  * @return  bool
  //  */
  // function delete(string $key): bool;

  // /**
  //  * Clear cache
  //  *
  //  * @return  bool
  //  */
  // function clear(): bool;

  // /**
  //  * Get multiple values at the same time
  //  *
  //  * @param   iterable $keys          A list of key => value pairs for a multiple-set operation.
  //  * @param   mixed $default          Default value to return if the key does not exist
  //  *
  //  * @return  iterable                Key=>value array with keys and the values retreived
  //  */
  // function getMultiple(iterable $keys, mixed $default = null): iterable;

  // /**
  //  * Set multiple values at the same time
  //  *
  //  * @param   iterable $values        A list of key => value pairs for a multiple-set operation.
  //  * @param   null|int|\DateInterval $ttl              Number of seconds to live. 0=infinite
  //  *
  //  * @return  array
  //  */
  // function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool;

  // /**
  //  * Delete multiple keys at once
  //  *
  //  * @param   iterable $keys          Array of keynames to delete
  //  *
  //  * @return  bool
  //  */
  // function deleteMultiple(iterable $keys): bool;

  // /**
  //  * Check if $key is in cache
  //  *
  //  * @param   string  $key          Name of key to check for
  //  *
  //  * @return  bool
  //  */
  // function has(string $key): bool;

  /**
   * Increment a $key's value and return the new value.
   *
   * @param   string      $key      Key name to increment
   * @param   int|integer $amount   Amount to increment with (default 1)
   *
   * @return  int|bool
   */
  function inc(string $key, int $amount=1);

  /**
   * Decrement a $key's value and return the new value.
   *
   * @param   string      $key      Key name to decrement
   * @param   int|integer $amount   Amount to decrement with (default 1)
   *
   * @return  int|bool
   */
  function dec(string $key, int $amount=1);

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
  function setOptions(array $options);

  /**
   * Get Driver Name
   * @return mixed
   */
  function getDriver(): string;

  /**
   * Set Driver Name
   * @param mixed $driver
   * @return self
   */
  function setDriver(string $driver);

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
  function setVersion(string $version);
}
