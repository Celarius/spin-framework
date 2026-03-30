<?php declare(strict_types=1);

namespace Spin\tests\Exceptions;

use PHPUnit\Framework\TestCase;
use Spin\Exceptions\CacheException;
use Spin\Exceptions\SpinException;

class CacheExceptionTest extends TestCase
{
  public function testCacheExceptionCanBeThrown(): void
  {
    $this->expectException(CacheException::class);
    throw new CacheException('Cache adapter not available');
  }

  public function testCacheExceptionExtendsSpinException(): void
  {
    $this->assertInstanceOf(SpinException::class, new CacheException('test'));
  }

  public function testCacheExceptionExtendsBaseException(): void
  {
    $this->assertInstanceOf(\Exception::class, new CacheException('test'));
  }

  public function testCacheExceptionPreservesMessage(): void
  {
    $e = new CacheException('Cache driver Redis not available');
    $this->assertSame('Cache driver Redis not available', $e->getMessage());
  }

  public function testCacheExceptionPreservesCode(): void
  {
    $this->assertSame(42, (new CacheException('error', 42))->getCode());
  }

  public function testCacheExceptionPreservesPrevious(): void
  {
    $previous = new \RuntimeException('original');
    $this->assertSame($previous, (new CacheException('wrapper', 0, $previous))->getPrevious());
  }

  public function testCacheExceptionCanBeCaughtAsSpinException(): void
  {
    $caught = null;
    try {
      throw new CacheException('test');
    } catch (SpinException $e) {
      $caught = $e;
    }
    $this->assertInstanceOf(CacheException::class, $caught);
  }
}
