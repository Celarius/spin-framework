<?php declare(strict_types=1);

/**
 * AbstractCacheAdapter base class
 *
 * Extend this for PSR-16 or PSR-6 Caches
 */

namespace Spin\Cache;

abstract class AbstractCacheAdapter implements AbstractCacheAdapterInterface
{
  /**
   * @var  array Driver Options from Config
   */
  protected array $options = [];

  /**
   * @var  string Driver name
   */
  protected string $driver = '';

  /**
   * @var  string Driver Version
   */
  protected string $version = '';

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

  public function initialize(): self
  {
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getOptions(): array
  {
      return $this->options;
  }

  /**
   * @inheritDoc
   */
  public function setOptions(array $options): \Spin\Cache\AbstractCacheAdapterInterface|self
  {
      $this->options = $options;

      return $this;
  }

  /**
   * @inheritDoc
   */
  public function getDriver(): string
  {
      return $this->driver;
  }

  /**
   * @inheritDoc
   */
  public function setDriver(string $driver): \Spin\Cache\AbstractCacheAdapterInterface|self
  {
      $this->driver = $driver;

      return $this;
  }

  /**
   * @inheritDoc
   */
  public function getVersion(): string
  {
      return $this->version;
  }

  /**
   * @inheritDoc
   */
  public function setVersion(string $version): \Spin\Cache\AbstractCacheAdapterInterface|self
  {
      $this->version = $version;

      return $this;
  }
}
