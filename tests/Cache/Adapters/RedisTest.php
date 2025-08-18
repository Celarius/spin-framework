<?php declare(strict_types=1);

namespace Spin\tests\Core;

use PHPUnit\Framework\TestCase;
use \Spin\Cache\Adapters\Redis;

class RedisTest extends TestCase
{
  protected Redis $cacheObj;

  public function setup(): void
  {
    $this->cacheObj = new Redis([
      'options' => [
        'host' => '127.0.0.1',
        'port' => 6379
      ]
    ]);
  }

  public function testRedisAdapterCreated()
  {
    $this->assertNotNull($this->cacheObj);
  }

}