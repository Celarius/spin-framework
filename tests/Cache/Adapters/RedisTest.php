<?php declare(strict_types=1);

namespace Spin\tests\Core;

use PHPUnit\Framework\TestCase;
use \Spin\Cache\Adapters\Redis;

class RedisTest extends TestCase
{
  protected $cacheObj;

  public function setup(): void
  {
    $this->cacheObj = new Redis([]);
  }

  public function testRedisAdapterCreated()
  {
    $this->assertNotNull( $this->cacheObj );
  }

}