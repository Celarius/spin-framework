<?php declare(strict_types=1);

/**
 * Container Factory
 *
 * This factory produces PSR-11 compliant The Leauge Containers.
 *
 * @link     http://container.thephpleague.com/
 * @package  Spin
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

    \logger()->debug('Created PSR-11 Container (The Leauge Container)');

    return $container;
  }

}
