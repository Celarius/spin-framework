<?php declare(strict_types=1);

/**
 * Cache Factory
 *
 * This factory produces PSR-16 compliant APCu caches.
 *
 * @package  Spin
 */

namespace Spin\Factories;

use \Spin\Factories\AbstractFactory;

// PSR-16
use \Spin\Cache\Drivers\Apcu;

class CacheFactory extends AbstractFactory
{
  /**
   * Create a new SimpleCache
   *
   * @return \Psr\SimpleCache\CacheInterface
   */
  public function createCache()
  {
    $cache = new Apcu( $this->getOptions() );

    logger()->debug('Created PSR-16 Cache: '.$cache->getDriver().' v'.$cache->getVersion());

    return $cache;
  }

}
