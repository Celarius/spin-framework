<?php declare(strict_types=1);

namespace Spin\tests\Core;

use PHPUnit\Framework\TestCase;
use \Spin\Cache\AbstractCacheAdapter;

# TestAdapter to extend the AbstractCacheAdapter class
class TestAdapter extends AbstractCacheAdapter
{
  public function get($key, $default = null)
  {
    return '';
  }

  public function set($key, $value, $ttl = null)
  {
    return false;
  }

  public function delete($key)
  {
    return false;
  }

  public function clear()
  {
    return false;
  }

  public function getMultiple($keys, $default = null)
  {
    return [];
  }

  public function setMultiple($values, $ttl = null)
  {
    return false;
  }

  public function deleteMultiple($keys)
  {
    return false;
  }

  public function has($key)
  {
    return false;
  }

  public function inc(string $key, int $amount=1)
  {
    return 0;
  }

  public function dec(string $key, int $amount=1)
  {
    return 0;
  }

  public function statistics(): array
  {
    return [];
  }
}


class AbstractCacheAdapterTest extends TestCase
{
  public function testTestAdapter()
  {
    $obj = new TestAdapter('');

    $this->assertFalse( \is_null($obj) );
  }

}