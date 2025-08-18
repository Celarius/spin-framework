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
use Predis\Client as RedisClient;

class Redis extends AbstractCacheAdapter
{
  /**
   * @var  RedisClient The Redis Client connection
   */
  protected RedisClient $redisClient;

  /**
   * @param array $connectionDetails
   * @param array $redisOptions
   */
  public function __construct(array $connectionDetails = [])
  {
    if (($connectionDetails['options'] ?? null) === null) {
      throw new \RuntimeException("Empty Redis connection options");
    }

    # Set $driver and $connectionDetails
    parent::__construct('Redis', $connectionDetails);
    # Create the client
    $this->redisClient = new RedisClient($connectionDetails['options']);
    # Set the version
    $this->setVersion((string)$this->redisClient::VERSION);
  }

  /**
   * Connect to redis
   *
   * @return bool
   */
  protected function connect(): bool
  {
    if (!$this->redisClient->isConnected()) {
      $this->redisClient->connect();
    }

    return $this->redisClient->isConnected();
  }

  /**
   * Disconnect from redis
   *
   * @return bool
   */
  protected function disconnect(): bool
  {
    if ($this->redisClient->isConnected()) {
      $this->redisClient->disconnect();
    }

    return !$this->redisClient->isConnected();
  }

  public function get($key, mixed $default = null): mixed
  {
    $result = $this->redisClient->get($key);
    if ($result) {
      return unserialize($result);
    }
    return $default;
  }

  /**
   * Returns raw values from Redis without unserializing the data.
   * If an object needs to be unserialized against its original class
   * the client should handle it.
   */
  public function getRaw($key, mixed $default = null): mixed
  {
    return $this->redisClient->get($key) ?? $default;
  }

  public function set($key, $value, \DateInterval|int|null $ttl = null): bool
  {
    if (is_null($ttl)) {
      return (bool)$this->redisClient->set($key, serialize($value));
    }
    if ($ttl instanceof \DateInterval) {
      $now = (new \DateTime('now'))->getTimestamp();
      $ttl = (new \DateTime('now'))->add($ttl)->getTimestamp() - $now;
    }
    return (bool)$this->redisClient->set($key, serialize($value), 'ex', $ttl);
  }

  public function delete($key): bool
  {
    return $this->redisClient->del($key) !== 0;
  }

  public function clear(): bool
  {
    return true;
  }

  public function getMultiple($keys, mixed $default = null): iterable
  {
    $values = array();
    foreach ($keys as $key) {
      $values[] = [$key => $this->get($key)];
    }

    return $values;
  }

  public function setMultiple($values, \DateInterval|int|null $ttl = null): bool
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

  public function has(string $key): bool
  {
    return $this->redisClient->exists( $key ) !== 0;
  }

  public function inc(string $key, int $amount = 1): bool|int
  {
    return $this->redisClient->incrby($key, $amount);
  }

  public function dec(string $key, int $amount = 1): bool|int
  {
    return $this->redisClient->dec( $key, $amount);
  }

  public function statistics(): array
  {
    return $this->redisClient->info();
  }
}
