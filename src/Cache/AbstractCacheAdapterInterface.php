<?php declare(strict_types=1);

namespace Spin\Cache;

interface AbstractCacheAdapterInterface
{
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
