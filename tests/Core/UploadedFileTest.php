<?php declare(strict_types=1);

namespace Spin\tests\Core;

use PHPUnit\Framework\TestCase;

use \Spin\Core\UploadedFile;

class UploadedFileTest extends TestCase
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
   * Test OpenSSL Encryption / Decryption
   */
  public function testUploadedFile(): void
  {
    $uFile = new UploadedFile([]);

    $this->assertNotNull($uFile);
  }

}