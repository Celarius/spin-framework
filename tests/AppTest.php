<?php declare(strict_types=1);

namespace Spin\tests;

use PHPUnit\Framework\TestCase;
use Spin\Exceptions\SpinException;

class AppTest extends TestCase
{
  /**
   * @var \Spin\Application
   */
  protected \Spin\Application $app;

  /**
   * Setup test
   */
  public function setUp(): void
  {
    global $app;

    $this->app = $app;
  }

  /**
   * Test Application object creation
   */
  public function testAppCreate(): void
  {
    $this->assertSame($this->app->getBasePath(), \realpath(__DIR__));
  }

  public function test_ApplicationLogger(): void
  {
    \logger()->notice('This is a logline', ['a' => '1']);

    $this->assertTrue(true);
  }

  /**
   * @throws SpinException
   */
  public function testRun(): void
  {
    $this->assertTrue($this->app->run());
  }

}