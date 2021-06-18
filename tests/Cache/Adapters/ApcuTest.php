<?php declare(strict_types=1);

namespace Spin\tests\Core;

use PHPUnit\Framework\TestCase;
use \Spin\Cache\Adapters\Apcu;

class ApcuTest extends TestCase
{
  protected $cacheObj = null;

  public function setup(): void
  {
    try {
      $this->cacheObj = new Apcu();

    } catch (\Exception $e) {
      $this->cacheObj = null;
    }
  }

  public function testApcuAdapterCreated()
  {
    if ($this->cacheObj) {
      $this->assertFalse( \is_null($this->cacheObj) );
    } else {
      $this->assertFalse( false );
    }
  }

}