<?php declare(strict_types=1);

namespace Spin\tests\Exceptions;

use PHPUnit\Framework\TestCase;
use Spin\Exceptions\DatabaseException;
use Spin\Exceptions\SpinException;

class DatabaseExceptionTest extends TestCase
{
  public function testDatabaseExceptionCanBeThrown(): void
  {
    $this->expectException(DatabaseException::class);
    throw new DatabaseException('Query execution failed');
  }

  public function testDatabaseExceptionExtendsSpinException(): void
  {
    $this->assertInstanceOf(SpinException::class, new DatabaseException('test'));
  }

  public function testDatabaseExceptionExtendsBaseException(): void
  {
    $this->assertInstanceOf(\Exception::class, new DatabaseException('test'));
  }

  public function testDatabaseExceptionPreservesMessage(): void
  {
    $e = new DatabaseException('SQLSTATE[HY000]: General error');
    $this->assertSame('SQLSTATE[HY000]: General error', $e->getMessage());
  }

  public function testDatabaseExceptionPreservesCode(): void
  {
    $this->assertSame(1045, (new DatabaseException('error', 1045))->getCode());
  }

  public function testDatabaseExceptionPreservesPrevious(): void
  {
    $previous = new \PDOException('pdo error');
    $this->assertSame($previous, (new DatabaseException('wrapper', 0, $previous))->getPrevious());
  }

  public function testDatabaseExceptionCanBeCaughtAsSpinException(): void
  {
    $caught = null;
    try {
      throw new DatabaseException('test');
    } catch (SpinException $e) {
      $caught = $e;
    }
    $this->assertInstanceOf(DatabaseException::class, $caught);
  }
}
