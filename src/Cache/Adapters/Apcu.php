<?php declare(strict_types=1);

/**
 * APCu Cache Driver
 *
 * @package  Spin
*/

namespace Spin\Cache\Adapters;

use Spin\Cache\AbstractCacheAdapter;
use Psr\SimpleCache\CacheInterface;

class Apcu extends AbstractCacheAdapter implements CacheInterface
{
  /**
   * Constructor
   *
   * @throws  Exception
   */
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

  /**
   * Get $key from cache
   *
   * @param  [type] $key     [description]
   * @param  [type] $default [description]
   * @return mixed
   */
  public function get($key, $default = null)
  {
    $success = false;
    $value = \apcu_fetch( $key, $success );

    return ( $success ? $value : $default );
  }

  /**
   * Set the $key to $value, with $ttl (default 0)
   *
   * @param      [type]  $key    [description]
   * @param      [type]  $value  [description]
   * @param      [type]  $ttl    [description]
   *
   * @return     bool
   */
  public function set($key, $value, $ttl = null)
  {
    $success = \apcu_store( $key, $value, (\is_null($ttl) ? 0 : (int) $ttl) );

    return $success;
  }

  /**
   * Delete the $key
   *
   * @param  [type] $key [description]
   * @return bool
   */
  public function delete($key)
  {
    $success = \apcu_delete( $key );

    return $success;
  }

  /**
   * Clear cache
   *
   * @return bool
   */
  public function clear()
  {
    return \apcu_clear_cache();
  }

  /**
   * Get multiple values at the same time
   *
   * @param  array $keys        A list of key => value pairs for a multiple-set operation.
   * @param  mixed $default     Default value to return if the key does not exist
   * @return array              Key=>value array with keys and the values retreived
   */
  public function getMultiple($keys, $default = null)
  {
    $values = array();
    foreach ($keys as $key) {
      $values[] = [$key => $this->get($key)];
    }

    return $values;
  }

  /**
   * Set multiple values at the same time
   *
   * @param  array $keys        A list of key => value pairs for a multiple-set operation.
   * @param  int $ttl           Number of seconds to live. 0=infinite
   * @return array
   */
  public function setMultiple($items, $ttl = null)
  {
    foreach ($items as $key=>$value) {
      $this->set($key,$value,(\is_null($ttl) ? 0 : (int) $ttl));
    }

    return true;
  }

  /**
   * Delete multiple keys at once
   *
   * @param  array $keys        Array of keynames to delete
   * @return bool
   */
  public function deleteMultiple($keys)
  {
    foreach ($keys as $key) {
      $this->delete($key);
    }

    return true;
  }

  /**
   * Check if $key is in cache
   *
   * @param  string  $key       Name of key to check for
   * @return bool
   */
  public function has($key)
  {
    $success = \apcu_exists( $key );

    return $success;
  }

  /**
   * Increment a $key's value and return the new value.
   *
   * @param  string      $key       Key name to increment
   * @param  int|integer $amount    Amount to increment with (default 1)
   * @return int|bool
   */
  public function inc(string $key, int $amount=1): int
  {
    $success = false;
    $value = \apcu_inc( $key, $amount, $success);

    return ( $success ? $value : false );
  }

  /**
   * Decrement a $key's value and return the new value.
   *
   * @param  string      $key       Key name to decrement
   * @param  int|integer $amount    Amount to decrement with (default 1)
   * @return int|bool
   */
  public function dec(string $key, int $amount=1): int
  {
    $success = false;
    $value = \apcu_dec( $key, $amount, $success);

    return ( $success ? $value : false );
  }

}
