<?php declare(strict_types=1);

/**
 * AbstractCacheAdapter base class
 *
 * Extend this for PSR-16 or PSR-6 Caches
 */

namespace Spin\Cache;

use Spin\Cache\AbstractCacheAdapterInterface;

abstract class AbstractCacheAdapter implements AbstractCacheAdapterInterface
{
  /** @var  array       Driver Options from Config */
  protected $options = [];

  /** @var  string      Driver name */
  protected $driver = '';

  /** @var  string      Driver Version */
  protected $version = '';

  /**
   * Constructor
   *
   * @param string  $driver         Driver name
   * @param array   $options        Optional. Array with driver options
   */
  public function __construct(string $driver, array $options=[])
  {
    $this->setDriver($driver);
    $this->setOptions($options);
  }

  abstract public function get($key, $default = null);
  abstract public function set($key, $value, $ttl = null);
  abstract public function delete($key);
  abstract public function clear();
  abstract public function getMultiple($keys, $default = null);
  abstract public function setMultiple($items, $ttl = null);
  abstract public function deleteMultiple($keys);
  abstract public function has($key);
  abstract public function inc(string $key, int $amount=1);
  abstract public function dec(string $key, int $amount=1);
  abstract public function statistics(): array;

  public function getOptions(): array
  {
      return $this->options;
  }

  public function setOptions(array $options)
  {
      $this->options = $options;

      return $this;
  }

  public function getDriver(): string
  {
      return $this->driver;
  }

  public function setDriver(string $driver)
  {
      $this->driver = $driver;

      return $this;
  }

  public function getVersion(): string
  {
      return $this->version;
  }

  public function setVersion(string $version)
  {
      $this->version = $version;

      return $this;
  }
}
