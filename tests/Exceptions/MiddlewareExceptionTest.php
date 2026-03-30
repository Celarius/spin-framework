<?php declare(strict_types=1);

namespace Spin\tests\Exceptions;

use PHPUnit\Framework\TestCase;
use Spin\Exceptions\MiddlewareException;
use Spin\Exceptions\SpinException;

class MiddlewareExceptionTest extends TestCase
{
  public function testMiddlewareExceptionCanBeThrown(): void
  {
    $this->expectException(MiddlewareException::class);
    throw new MiddlewareException('Middleware initialization failed');
  }

  public function testMiddlewareExceptionExtendsSpinException(): void
  {
    $this->assertInstanceOf(SpinException::class, new MiddlewareException('test'));
  }

  public function testMiddlewareExceptionExtendsBaseException(): void
  {
    $this->assertInstanceOf(\Exception::class, new MiddlewareException('test'));
  }

  public function testMiddlewareExceptionPreservesMessage(): void
  {
    $e = new MiddlewareException('Authentication middleware failed to initialize');
    $this->assertSame('Authentication middleware failed to initialize', $e->getMessage());
  }

  public function testMiddlewareExceptionPreservesCode(): void
  {
    $this->assertSame(403, (new MiddlewareException('error', 403))->getCode());
  }

  public function testMiddlewareExceptionPreservesPrevious(): void
  {
    $previous = new \RuntimeException('inner error');
    $this->assertSame($previous, (new MiddlewareException('wrapper', 0, $previous))->getPrevious());
  }

  public function testMiddlewareExceptionCanBeCaughtAsSpinException(): void
  {
    $caught = null;
    try {
      throw new MiddlewareException('test');
    } catch (SpinException $e) {
      $caught = $e;
    }
    $this->assertInstanceOf(MiddlewareException::class, $caught);
  }
}
