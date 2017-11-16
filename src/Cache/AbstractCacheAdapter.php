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
  /** @var array Driver Options from Config */
  protected $options = [];
  /** @var string Driver name */
  protected $driver = '';
  /** @var string Driver Version */
  protected $version = '';

  /**
   * Constructor
   *
   * @param string $driver  [description]
   * @param array  $options [description]
   */
  public function __construct(string $driver, array $options=[])
  {
    $this->setDriver($driver);
    $this->setOptions($options);
  }

  /**
   * @return mixed
   */
  public function getOptions(): array
  {
      return $this->options;
  }

  /**
   * @param mixed $options
   *
   * @return self
   */
  public function setOptions(array $options)
  {
      $this->options = $options;

      return $this;
  }

  /**
   * @return mixed
   */
  public function getDriver(): string
  {
      return $this->driver;
  }

  /**
   * @param mixed $driver
   *
   * @return self
   */
  public function setDriver(string $driver)
  {
      $this->driver = $driver;

      return $this;
  }

  /**
   * @return mixed
   */
  public function getVersion(): string
  {
      return $this->version;
  }

  /**
   * @param mixed $version
   *
   * @return self
   */
  public function setVersion(string $version)
  {
      $this->version = $version;

      return $this;
  }
}
