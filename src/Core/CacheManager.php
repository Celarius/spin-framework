<?php declare(strict_types=1);

/**
 * CacheManager
 *
 * Manager for Cache integrations
 */

/*
Example:
  # Use 1st available Cache listed, and get 'key' from it
  $value = cache()->get('key');

  # Use 'remote.redis' cache, and get 'key' from it
  $value = cache('remote.redis')->get('key');
*/

namespace Spin\Core;

use Spin\Core\AbstractBaseClass;
use Spin\Core\CacheManagerInterface;
use Spin\Cache\AbstractCacheAdapter;
use Spin\Cache\AbstractCacheAdapterInterface;

class CacheManager extends AbstractBaseClass implements CacheManagerInterface
{
  /** @var array List of Instantiated Caches */
  protected $caches = [];

  /**
   * Get or Create a Cache
   *
   * @param  string $name         Name of the Cache (from Config)
   * @return null | object
   */
  public function getCache(string $name=null)
  {
    # Find the cache (if we already have it created)
    $cache = $this->findCache($name);

    if (is_null($cache)) {
      # Attempt to create the cache
      $cache = $this->createCache($name);

      if (!is_null($cache)) {
        $this->addCache($cache);
      }
    }

    return $cache;
  }

  /**
   * Find a Cache based on name
   *
   * If the $name is empty/null we'll return the 1st
   * cache in the internal list (if there is one)
   *
   * @param  string   $name       Name of the cache (from Config)
   * @return null | PdoConnection
   */
  public function findCache(string $name=null)
  {
    if ( empty($name) ) {
      # Take first available
      $cache = reset($this->caches);
      if ($cache === false)
        return null;
    } else {
      # Attempt to find the cache from the list
      $cache = ( $this->caches[strtolower($name)] ?? null);
    }

    return $cache;
  }

  /**
   * Adds the Cache to the Pool
   *
   * @param [type] $cache [description]
   * @return  connection
   */
  public function addCache(AbstractCacheAdapterInterface $cache)
  {
    $this->caches[strtolower($cache->getDriver())] = $cache;

    return $cache;
  }

  /**
   * Remove a cache from the pool
   *
   * @param  [type] $cache Name of cache to remove
   * @return bool
   */
  public function removeCache(string $name)
  {
    # Sanity check
    if (empty($name)) return false;

    $cache = $this->findCache($name);

    if ($cache) {
      unset( $this->caches[strtolower($cache->getDriver())] );
      unset($cache);
      $cache = null;
    }

    return is_null($cache);
  }

  /**
   * Creates a cache based on the $name
   *
   * Finds the corresponding name in the config and uses it
   * to instanciate a cache. If the $name is empty, we will use
   * the 1st available in the caches list.
   *
   * @param  string $name [description]
   * @return null | AbstractCacheAdapter
   */
  protected function createCache(string $name)
  {
    # Try to find the connection in the internal list, if it was created already
    $cache = $this->caches[strtolower($name)] ?? null;

    # If no connection found, and the $name is empty, read in 1st one
    if (is_null($cache) && empty($name)) {
      # Get caches from conf
      $arr = config()->get('caches');
      reset($arr);
      # Take the 1st caches name
      $name = key($arr);
    }

    if (is_null($cache)) {
      # Get connection's params from conf
      $conf = config()->get('caches.'.$name);

      # Instantiate either based on CLASS or the ADAPTER name
      if (isset($conf['class']) && !empty($conf['class'])) {
        $className = $conf['class'];
      } else {
        $className = '\\Spin\\Cache\\Adapters\\'.ucfirst($conf['adapter'] ?? '') ;
      }

      # Create the Cache
      try {
        if (class_exists($className)) {
          $cache = new $className($conf);
          logger()->debug( 'Created Cache', ['adapter'=>$cache->getDriver(),'version'=>$cache->getVersion()] );
        } else {
          logger()->error( 'Cache class does not exist', ['name'=>$name,'config'=>$conf] );
        }

      } catch (\Exception $e) {
        logger()->critical( $e->getMessage(), ['trace'=>$e->getTraceAsString()] );
      }
    }

    return $cache;
  }

  /**
   * Get array of containers
   *
   * @return array
   */
  public function getCaches(): array
  {
    return $this->caches;
  }

}
