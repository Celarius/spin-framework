<?php declare(strict_types=1);

/**
 * APCu Cache Driver
 *
 * Implements Psr\SimpleCache\CacheInterface methods
 *
 * @package  Spin
*/

namespace Spin\Cache\Adapters;

use Spin\Cache\AbstractCacheAdapter;

class Apcu extends AbstractCacheAdapter
{
  public function __construct(array $options = [])
  {
    # Set $driver and $options
    parent::__construct('APCu', $options);

    # Check if APCu extension is loaded and available
    if (!\extension_loaded('apcu') || \ini_get('apc.enabled') !== '1') {
      throw new \RuntimeException(sprintf('Cache driver %s not available', $this->getDriver()));
    }

    # Set the version of the APCu library
    $this->setVersion(\phpversion('apcu'));
  }

  /**
   * @inheritDoc
   */
  public function get($key, mixed $default = null): mixed
  {
    $success = false;
    $value = \apcu_fetch($key, $success);

    return ($success ? $value : $default);
  }

  /**
   * @inheritDoc
   */
  public function set($key, $value, \DateInterval|int|null $ttl = null): bool
  {
    return \apcu_store($key, $value, (\is_null($ttl) ? 0 : (int) $ttl));
  }

  /**
   * @inheritDoc
   */
  public function delete($key): bool
  {
    return  \apcu_delete($key);
  }

  /**
   * @inheritDoc
   */
  public function clear(): bool
  {
    return \apcu_clear_cache();
  }

  /**
   * @inheritDoc
   */
  public function getMultiple($keys, mixed $default = null): iterable
  {
    $values = array();
    foreach ($keys as $key) {
      $values[] = [$key => $this->get($key)];
    }

    return $values;
  }

  /**
   * @inheritDoc
   */
  public function setMultiple($values, \DateInterval|int|null $ttl = null): bool
  {
    foreach ($values as $key => $value) {
      $this->set($key, $value, (\is_null($ttl) ? 0 : (int) $ttl));
    }

    return true;
  }

  /**
   * @inheritDoc
   */
  public function deleteMultiple(iterable $keys): bool
  {
    foreach ($keys as $key) {
      $this->delete($key);
    }

    return true;
  }

  /**
   * @inheritDoc
   */
  public function has(string $key): bool
  {
    return \apcu_exists($key);
  }

  /**
   * @inheritDoc
   */
  public function inc(string $key, int $amount = 1): bool|int
  {
    $success = true;
    $value = \apcu_inc($key, $amount, $success);

    return ($success ? $value : false);
  }

  /**
   * @inheritDoc
   */
  public function dec(string $key, int $amount = 1): bool|int
  {
    $success = false;
    $value = \apcu_dec($key, $amount, $success);

    return ($success ? $value : false);
  }

  /**
   * @inheritDoc
   */
  public function statistics(): array
  {
    return apcu_cache_info();
  }
}
