<?php declare(strict_types=1);

namespace Spin;

use PHPUnit\Framework\TestCase;

use \Spin\Core\UploadedFilesManager;

class UploadedFilesManagerTest extends TestCase
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
  public function testUploadedFilesManager()
  {
    $manager = new UploadedFilesManager($_FILES);

    $this->assertTrue( !is_null($manager) );
  }

}