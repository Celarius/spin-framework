<?php declare(strict_types=1);

/**
 * Redis Cache Driver
 *
 * Implements Psr\SimpleCache\CacheInterface methods
 *
 * @package   Spin
 *
 * @link      https://github.com/predis/predis
*/

/*
// Redis connection details

$redis = new Redis([
    'scheme'          => 'tcp',
    'host'            => '127.0.0.1',
    'port'            => 6379,
    'connectTimeout'  => 2.5,
    'auth'            => ['phpredis', 'phpredis'],
    'ssl'             => ['verify_peer' => false],
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


  public function __construct(array $connectionDetails=[], array $redisOptions=[])
  {
    # Set $driver and $connectionDetails
    parent::__construct('Redis', $connectionDetails);

    # Create the client
    $this->redisClient = new RedisClient($connectionDetails, $redisOptions);

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

  public function get(string $key, mixed $default = null): mixed
  {
    return $this->redisClient->get( $key ) ?? $default;
  }

  public function set(string $key, mixed $value, mixed $ttl = null): bool
  {
    return $this->redisClient->set( $key, $value, null, (\is_null($ttl) ? 0 : (int) $ttl) );
  }

  public function delete(string $key): bool
  {
    return  $this->redisClient->del( $key ) != 0;
  }

  public function clear(): bool
  {
    return true;
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
    return $this->redisClient->exists( $key ) != 0;
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
