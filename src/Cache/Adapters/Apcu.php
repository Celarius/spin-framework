<?php declare(strict_types=1);

/**
 * APCu Cache Driver
 *
 * Implements Psr\SimpleCache\CacheInterface methods
 *
 * @package  Spin
*/

namespace Spin\Cache\Adapters;

use Spin\Cache\AbstractCacheAdapter;
use Psr\SimpleCache\CacheInterface;

class Apcu extends AbstractCacheAdapter implements CacheInterface
{
  public function __construct(array $options=[])
  {
    # Set $driver and $options
    parent::__construct('APCu',$options);

    # Check if APCu extension is loaded and available
    if ( ( \extension_loaded('apcu') == false ) ||
         ( \ini_get('apc.enabled') != '1' ) )
    {
      throw new \Exception('Cache driver '.$this->getDriver().' not available');
    }

    # Set the version of the APCu library
    $this->setVersion( \phpversion('apcu') );
  }

  public function get(string $key, mixed $default = null): mixed
  {
    $success = false;
    $value = \apcu_fetch( $key, $success );

    return ( $success ? $value : $default );
  }

  public function set(string $key, mixed $value, mixed $ttl = null): bool
  {
    return \apcu_store( $key, $value, (\is_null($ttl) ? 0 : (int) $ttl) );
  }

  public function delete(string $key): bool
  {
    return  \apcu_delete( $key );
  }

  public function clear(): bool
  {
    return \apcu_clear_cache();
  }

  public function getMultiple(iterable $keys, mixed $default = null): iterable
  {
    $values = array();
    foreach ($keys as $key) {
      $values[] = [$key => $this->get($key)];
    }

    return $values;
  }

  public function setMultiple(iterable $values, mixed $ttl = null): bool
  {
    foreach ($values as $key=>$value) {
      $this->set($key, $value, (\is_null($ttl) ? 0 : (int) $ttl));
    }

    return true;
  }

  public function deleteMultiple(iterable $keys): bool
  {
    foreach ($keys as $key) {
      $this->delete($key);
    }

    return true;
  }

  public function has($key): bool
  {
    return \apcu_exists( $key );
  }

  public function inc(string $key, int $amount=1)
  {
    $success = false;
    $value = \apcu_inc($key, $amount, $success);

    return ( $success ? $value : false );
  }

  public function dec(string $key, int $amount=1)
  {
    $success = false;
    $value = \apcu_dec( $key, $amount, $success);

    return ( $success ? $value : false );
  }

  public function statistics(): array
  {
    return apcu_cache_info();
  }
}
