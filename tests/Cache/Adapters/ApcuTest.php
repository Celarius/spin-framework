<?php declare(strict_types=1);

namespace Spin\tests\Core;

use PHPUnit\Framework\TestCase;

use \Spin\Cache\Adapters\Apcu;

class ApcuTest extends TestCase
{
  public function testTestAdapter()
  {
    $obj = new Apcu();

    $this->assertFalse( \is_null($obj) );
  }

}