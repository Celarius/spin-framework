<?php declare(strict_types=1);

/**
 * PSR-11 Container Factory Class
 *
 * Factory for creating PSR-11 compliant League Container instances with
 * optional auto-wiring support. Provides dependency injection container
 * creation with configurable options.
 *
 * @package  Spin\Factories
 * @author   Spin Framework Team
 * @since    1.0.0
 * @link     http://container.thephpleague.com/
 */

namespace Spin\Factories;

use \Spin\Factories\AbstractFactory;

# PSR-11
use \Psr\Container\ContainerInterface;

# The Leauge Container
use \League\Container\Container;
use \League\Container\ReflectionContainer;

class ContainerFactory extends AbstractFactory
{
  /**
   * Create a new container
   *
   * @return     \Psr\Container\ContainerInterface
   */
  public function createContainer()
  {
    # Create the container
    $container = new Container;

    # If we have auto-wire option on ...
    if (($this->options['autowire'] ?? false)) {
      # Add reflection delegate, so auto-wiring is possible
      $container->delegate( new ReflectionContainer );
    }

    \logger()->debug('Created PSR-11 Container (The League Container)');

    return $container;
  }

}
