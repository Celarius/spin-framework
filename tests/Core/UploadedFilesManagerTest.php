<?php declare(strict_types=1);

namespace Spin\tests\Core;

use PHPUnit\Framework\TestCase;

use \Spin\Core\UploadedFilesManager;

class UploadedFilesManagerTest extends TestCase
{
  /**
   * @var \Spin\Application
   */
  protected \Spin\Application $app;

  /**
   * Setup
   */
  public function setUp(): void
  {
    global $app;
    $this->app = $app;
  }

  /**
   * Test
   */
  public function testUploadedFilesManager(): void
  {
    $manager = new UploadedFilesManager($_FILES);

    $this->assertNotNull($manager);
  }

  /**
   * Test
   */
  public function testUploadedFilesManagerFiles(): void
  {
    $manager = new UploadedFilesManager($_FILES);

    $files = $manager->getFiles();

    $this->assertIsArray($files);
  }

}