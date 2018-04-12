<?php declare(strict_types=1);

/**
 * AbstractFactoryInterface
 * 
 * @package  Spin
 */

namespace Spin\Factories;

interface AbstractFactoryInterface
{
  /**
   * Gets the options.
   *
   * @return     mixed
   */
  function getOptions(): array;

  /**
   * Sets the options.
   *
   * @param      mixed  $options
   *
   * @return     self
   */
  function setOptions(array $options);
}
