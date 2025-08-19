<?php declare(strict_types=1);

namespace Spin\tests\Core;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\InvalidArgumentException;
use Spin\Cache\Adapters\Redis;

class RedisTest extends TestCase
{
  protected ?Redis $cacheObj = null;

  public function setUp(): void
  {
    try {
      $this->cacheObj = new Redis([
        'options' => [
          'host' => '127.0.0.1',
          'port' => 6379
        ]
      ]);
      // Force connectivity check; skip if not reachable
      $this->cacheObj->statistics();
      // Clear any existing data before tests
      $this->cacheObj->clear();
    } catch (\Exception $e) {
      $this->markTestSkipped('Redis server is not available: ' . $e->getMessage());
    }
  }

  public function tearDown(): void
  {
    if ($this->cacheObj !== null) {
      // Clean up after tests
      $this->cacheObj->clear();
    }
  }

  public function testRedisAdapterCreated(): void
  {
    $this->assertNotNull($this->cacheObj);
    $this->assertInstanceOf(Redis::class, $this->cacheObj);
  }

  public function testRedisConnectionOptionsRequired(): void
  {
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('Empty Redis connection options');
    
    new Redis([]);
  }

  /**
   * @throws InvalidArgumentException
   */
  public function testSetAndGetValue(): void
  {
    $key = 'test_key';
    $value = 'test_value';
    
    $result = $this->cacheObj->set($key, $value);
    $this->assertTrue($result);
    
    $retrieved = $this->cacheObj->get($key);
    $this->assertEquals($value, $retrieved);
  }

  /**
   * @throws InvalidArgumentException
   */
  public function testSetAndGetComplexValue(): void
  {
    $key = 'complex_key';
    $value = [
      'name' => 'Test',
      'data' => ['a' => 1, 'b' => 2],
      'object' => new \stdClass()
    ];
    
    $result = $this->cacheObj->set($key, $value);
    $this->assertTrue($result);
    
    $retrieved = $this->cacheObj->get($key);
    $this->assertEquals($value, $retrieved);
  }

  /**
   * @throws InvalidArgumentException
   */
  public function testGetWithDefault(): void
  {
    $key = 'non_existent_key';
    $default = 'default_value';
    
    $result = $this->cacheObj->get($key, $default);
    $this->assertEquals($default, $result);
  }

  /**
   * @throws InvalidArgumentException
   */
  public function testGetRaw(): void
  {
    $key = 'raw_key';
    $value = 'test_value';
    
    $this->cacheObj->set($key, $value);
    
    $rawValue = $this->cacheObj->getRaw($key);
    $this->assertEquals(serialize($value), $rawValue);
    
    $nonExistent = $this->cacheObj->getRaw('non_existent', 'default');
    $this->assertEquals('default', $nonExistent);
  }

  /**
   * @throws InvalidArgumentException
   */
  public function testSetWithTtlInt(): void
  {
    $key = 'ttl_key';
    $value = 'ttl_value';
    $ttl = 60; // 60 seconds
    
    $result = $this->cacheObj->set($key, $value, $ttl);
    $this->assertTrue($result);
    
    $retrieved = $this->cacheObj->get($key);
    $this->assertEquals($value, $retrieved);
  }

  /**
   * @throws InvalidArgumentException
   */
  public function testSetWithTtlDateInterval(): void
  {
    $key = 'interval_key';
    $value = 'interval_value';
    $ttl = new \DateInterval('PT60S'); // 60 seconds
    
    $result = $this->cacheObj->set($key, $value, $ttl);
    $this->assertTrue($result);
    
    $retrieved = $this->cacheObj->get($key);
    $this->assertEquals($value, $retrieved);
  }

  /**
   * @throws InvalidArgumentException
   */
  public function testDelete(): void
  {
    $key = 'delete_key';
    $value = 'delete_value';
    
    $this->cacheObj->set($key, $value);
    $this->assertTrue($this->cacheObj->has($key));
    
    $result = $this->cacheObj->delete($key);
    $this->assertTrue($result);
    $this->assertFalse($this->cacheObj->has($key));
    
    // Deleting non-existent key
    $result = $this->cacheObj->delete('non_existent');
    $this->assertFalse($result);
  }

  public function testClear(): void
  {
    $result = $this->cacheObj->clear();
    $this->assertTrue($result);
  }

  /**
   * @throws InvalidArgumentException
   */
  public function testHas(): void
  {
    $key = 'has_key';
    $value = 'has_value';
    
    $this->assertFalse($this->cacheObj->has($key));
    
    $this->cacheObj->set($key, $value);
    $this->assertTrue($this->cacheObj->has($key));
    
    $this->cacheObj->delete($key);
    $this->assertFalse($this->cacheObj->has($key));
  }

  /**
   * @throws InvalidArgumentException
   */
  public function testGetMultiple(): void
  {
    $data = [
      'key1' => 'value1',
      'key2' => 'value2',
      'key3' => 'value3'
    ];
    
    foreach ($data as $key => $value) {
      $this->cacheObj->set($key, $value);
    }
    
    $keys = array_keys($data);
    $result = $this->cacheObj->getMultiple($keys);
    
    $this->assertIsIterable($result);
    
    $retrieved = [];
    foreach ($result as $item) {
      foreach ($item as $key => $value) {
        $retrieved[$key] = $value;
      }
    }
    
    foreach ($data as $key => $value) {
      $this->assertArrayHasKey($key, $retrieved);
      $this->assertEquals($value, $retrieved[$key]);
    }
  }

  /**
   * @throws InvalidArgumentException
   */
  public function testSetMultiple(): void
  {
    $data = [
      'multi1' => 'value1',
      'multi2' => 'value2',
      'multi3' => 'value3'
    ];
    
    $result = $this->cacheObj->setMultiple($data);
    $this->assertTrue($result);
    
    foreach ($data as $key => $value) {
      $this->assertEquals($value, $this->cacheObj->get($key));
    }
  }

  /**
   * @throws InvalidArgumentException
   */
  public function testSetMultipleWithTtl(): void
  {
    $data = [
      'ttl_multi1' => 'value1',
      'ttl_multi2' => 'value2'
    ];
    
    $result = $this->cacheObj->setMultiple($data, 60);
    $this->assertTrue($result);
    
    foreach ($data as $key => $value) {
      $this->assertEquals($value, $this->cacheObj->get($key));
    }
  }

  /**
   * @throws InvalidArgumentException
   */
  public function testDeleteMultiple(): void
  {
    $data = [
      'del_multi1' => 'value1',
      'del_multi2' => 'value2',
      'del_multi3' => 'value3'
    ];
    
    $this->cacheObj->setMultiple($data);
    
    $keys = array_keys($data);
    $result = $this->cacheObj->deleteMultiple($keys);
    $this->assertTrue($result);
    
    foreach ($keys as $key) {
      $this->assertFalse($this->cacheObj->has($key));
    }
  }

  public function testIncrement(): void
  {
    $key = 'counterTestIncrement';
    
    // First increment (key doesn't exist, should create with value 1)
    $result = $this->cacheObj->inc($key);
    $this->assertEquals(1, $result);
    
    // Second increment (should be 2)
    $result = $this->cacheObj->inc($key);
    $this->assertEquals(2, $result);
    
    // Increment by 5 (should be 7)
    $result = $this->cacheObj->inc($key, 5);
    $this->assertEquals(7, $result);
  }

  /**
   * @throws InvalidArgumentException
   */
  public function testDecrement(): void
  {
    $key = 'dec_counter_testDecrement';
    
    // Set initial value
    $this->cacheObj->set($key, 10);
    
    // Decrement by 1 (should be 9)
    $result = $this->cacheObj->dec($key);
    $this->assertEquals(9, $result);
    
    // Decrement by 3 (should be 6)
    $result = $this->cacheObj->dec($key, 3);
    $this->assertEquals(6, $result);
  }

  public function testStatistics(): void
  {
    $stats = $this->cacheObj->statistics();
    
    $this->assertIsArray($stats);
    $this->assertNotEmpty($stats);
    
    // Redis info() returns various sections
    // We can check for common keys like Server info
    $this->assertArrayHasKey('Server', $stats);
  }

  public function testVersion(): void
  {
    $version = $this->cacheObj->getVersion();
    
    $this->assertIsString($version);
    $this->assertNotEmpty($version);
  }

  public function testDriverName(): void
  {
    $driver = $this->cacheObj->getDriver();
    
    $this->assertEquals('Redis', $driver);
  }

  /**
   * Test that values persist across different instances
   *
   * @throws InvalidArgumentException
   */
  public function testPersistence(): void
  {
    $key = 'persist_key';
    $value = 'persist_value';
    
    // Set value with first instance
    $this->cacheObj->set($key, $value);
    
    // Create new instance
    $newInstance = new Redis([
      'options' => [
        'host' => '127.0.0.1',
        'port' => 6379
      ]
    ]);
    
    // Value should still be available
    $retrieved = $newInstance->get($key);
    $this->assertEquals($value, $retrieved);
  }
}