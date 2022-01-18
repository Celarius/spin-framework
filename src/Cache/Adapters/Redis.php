<?php declare(strict_types=1);

/**
 * Redis Cache Driver
 *
 * @package   Spin
 *
 * @link      https://github.com/predis/predis
*/

/*
// Redis connection details

$redis = new Redis([
    'host' => '127.0.0.1',
    'port' => 6379,
    'connectTimeout' => 2.5,
    'auth' => ['phpredis', 'phpredis'],
    'ssl' => ['verify_peer' => false],
]);
*/

namespace Spin\Cache\Adapters;

use Spin\Cache\AbstractCacheAdapter;
use Psr\SimpleCache\CacheInterface;
use Predis\Client as RedisClient;

class Redis extends AbstractCacheAdapter implements CacheInterface
{
  /** @var  RedisClient         The Redis Client connection */
  protected $redisClient;


  public function __construct(array $options=[])
  {
    # Set $driver and $options
    parent::__construct('Redis', $options);

    # Create the client
    $this->redisClient = new RedisClient($options);

    # Set the version
    $this->setVersion( $this->redisClient::VERSION );

    // # Connect
    // $this->connect();      // Connections are established lazily on 1st command
  }

  public function initialize()
  {
    return parent::initialize();
  }

  /**
   * Connect to redis
   *
   * @return bool
   */
  protected function connect()
  {
    if (!$this->redisClient->isConnected()) $this->redisClient->connect();

    return $this->redisClient->isConnected();
  }

  /**
   * Disconnect from redis
   *
   * @return bool
   */
  protected function disconnect()
  {
    if ($this->redisClient->isConnected()) $this->redisClient->disconnect();

    return !$this->redisClient->isConnected();
  }

  public function get($key, $default = null)
  {
    return $this->redisClient->get( $key ) ?? $default;
  }

  public function set($key, $value, $ttl = null)
  {
    return $this->redisClient->set( $key, $value, null, (\is_null($ttl) ? 0 : (int) $ttl) );
  }

  public function delete($key)
  {
    return  $this->redisClient->del( $key );
  }

  public function clear()
  {
    return true;
  }

  public function getMultiple($keys, $default = null)
  {
    $values = array();
    foreach ($keys as $key) {
      $values[] = [$key => $this->get($key)];
    }

    return $values;
  }

  public function setMultiple($items, $ttl = null)
  {
    foreach ($items as $key=>$value) {
      $this->set($key, $value, (\is_null($ttl) ? 0 : (int) $ttl));
    }

    return true;
  }

  public function deleteMultiple($keys)
  {
    foreach ($keys as $key) {
      $this->delete($key);
    }

    return true;
  }

  public function has($key)
  {
    return $this->redisClient->exists( $key );
  }

  public function inc(string $key, int $amount=1)
  {
    return $this->redisClient->incrby($key, $amount);
  }

  public function dec(string $key, int $amount=1)
  {
    return $this->redisClient->dec( $key, $amount);
  }

  public function statistics(): array
  {
    return $this->redisClient->info();
  }
}
