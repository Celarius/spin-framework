<?php declare(strict_types=1);

/**
 * Abstract Factory Interface
 *
 * Defines the contract for factory implementations providing common options
 * management and configuration handling. Implemented by AbstractFactory to
 * provide framework factory capabilities.
 *
 * @package  Spin\Factories
 * @author   Spin Framework Team
 * @since    1.0.0
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
