<?php declare(strict_types=1);

namespace Spin\tests\Core;

use PHPUnit\Framework\TestCase;
use \Spin\Cache\Adapters\Apcu;

class ApcuTest extends TestCase
{
  protected $cacheObj;

  public function setup(): void
  {
    $this->cacheObj = new Apcu();
  }

  public function testApcuAdapterCreated()
  {
    $this->assertNotNull( $this->cacheObj );
  }

}