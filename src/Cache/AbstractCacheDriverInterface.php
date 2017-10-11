<?php declare(strict_types=1);

namespace Spin\Cache;

interface AbstractCacheDriver
{
  /**
   * Constructor
   *
   * @param string $driver  [description]
   * @param array  $options [description]
   */
  function __construct(string $driver, array $options=[]);

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
   * @return mixed
   */
  function getDriver(): string;

  /**
   * @param mixed $driver
   *
   * @return self
   */
  function setDriver(string $driver);

  /**
   * @return mixed
   */
  function getVersion(): string;

  /**
   * @param mixed $version
   *
   * @return self
   */
  function setVersion(string $version);
}
