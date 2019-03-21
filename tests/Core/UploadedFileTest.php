<?php declare(strict_types=1);

namespace Spin\tests\Core;

use PHPUnit\Framework\TestCase;

use \Spin\Core\UploadedFile;

class UploadedFileTest extends TestCase
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
   * Test OpenSSL Encryption / Decryption
   */
  public function testUploadedFile()
  {
    $uFile = new UploadedFile([]);

    $this->assertTrue( !is_null($uFile) );
  }

}