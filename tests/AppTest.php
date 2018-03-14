<?php declare(strict_types=1);

namespace Spin;

use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
  /** @var        string          Application object */
  protected $app;

  /**
   * Setup test
   */
  public function setup()
  {
    global $app;

    $this->app = $app;
  }

  /**
   * Test Application object creation
   */
  public function testAppCreate()
  {
    $this->assertSame($this->app->getBasePath(), realpath(__DIR__));
  }

}