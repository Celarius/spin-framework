<?php declare(strict_types=1);

namespace Spin\tests\Core;

use PHPUnit\Framework\TestCase;
use \Spin\Cache\AbstractCacheAdapter;

# TestAdapter to extend the AbstractCacheAdapter class
class TestAdapter extends AbstractCacheAdapter
{
  public function get($key, mixed $default = null): mixed
  {
    return '';
  }

  public function set($key, $value, \DateInterval|int|null $ttl = null): bool
  {
    return false;
  }

  public function delete($key): bool
  {
    return false;
  }

  public function clear(): bool
  {
    return false;
  }

  public function getMultiple($keys, mixed $default = null): iterable
  {
    return [];
  }

  public function setMultiple($values, \DateInterval|int|null $ttl = null): bool
  {
    return false;
  }

  public function deleteMultiple(iterable $keys): bool
  {
    return false;
  }

  public function has(string $key): bool
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