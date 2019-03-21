<?php declare(strict_types=1);

namespace Spin\tests\Core;

use PHPUnit\Framework\TestCase;

use \Spin\Cache\AbstractCacheAdapter;

class TestAdapter extends AbstractCacheAdapter
{
}


class AbstractCacheAdapterTest extends TestCase
{
  public function testTestAdapter()
  {
    $obj = new TestAdapter('');

    $this->assertFalse( \is_null($obj) );
  }

}