<?php declare(strict_types=1);

namespace Spin\tests\Exceptions;

use PHPUnit\Framework\TestCase;
use Spin\Exceptions\ConfigException;
use Spin\Exceptions\SpinException;

class ConfigExceptionTest extends TestCase
{
  public function testConfigExceptionCanBeThrown(): void
  {
    $this->expectException(ConfigException::class);
    throw new ConfigException('Invalid JSON file config.json');
  }

  public function testConfigExceptionExtendsSpinException(): void
  {
    $this->assertInstanceOf(SpinException::class, new ConfigException('test'));
  }

  public function testConfigExceptionExtendsBaseException(): void
  {
    $this->assertInstanceOf(\Exception::class, new ConfigException('test'));
  }

  public function testConfigExceptionPreservesMessage(): void
  {
    $e = new ConfigException('Invalid JSON file "config-production.json"');
    $this->assertSame('Invalid JSON file "config-production.json"', $e->getMessage());
  }

  public function testConfigExceptionPreservesCode(): void
  {
    $this->assertSame(500, (new ConfigException('error', 500))->getCode());
  }

  public function testConfigExceptionPreservesPrevious(): void
  {
    $previous = new \JsonException('json error');
    $this->assertSame($previous, (new ConfigException('wrapper', 0, $previous))->getPrevious());
  }

  public function testConfigExceptionCanBeCaughtAsSpinException(): void
  {
    $caught = null;
    try {
      throw new ConfigException('test');
    } catch (SpinException $e) {
      $caught = $e;
    }
    $this->assertInstanceOf(ConfigException::class, $caught);
  }
}
