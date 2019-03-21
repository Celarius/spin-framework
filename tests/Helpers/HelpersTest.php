<?php declare(strict_types=1);

namespace Spin\tests\Helpers;

use PHPUnit\Framework\TestCase;

use \Spin\Helpers\Cipher;
use \Spin\Helpers\Hash;
use \Spin\Helpers\UUID;

class HelpersTest extends TestCase
{
  /** @var        string          Application object */
  protected $app;

  protected $secret;

  /**
   * Setup test
   */
  public function setup()
  {
    global $app;

    $this->app = $app;

    # Set a Secret for the testing
    $this->secret = 'There be dragons here';
  }

  /**
   * Test OpenSSL Encryption / Decryption
   */
  public function testCipher()
  {
    $plain = 'Let this be the light';
    $encrypted = Cipher::encrypt( $plain, $this->secret );
    $a = Cipher::decrypt( $encrypted, $this->secret );

    $this->assertEquals($plain, $a);
  }

  /**
   * Test OpenSSL Message Digest (SHA256)
   */
  public function testHash()
  {
    $plain = 'Let this be the light';
    $hash  = '3c47c0efe106197074e89d6eb28babb90d2ad6fcc5dd7b37fec77b3bb00003d0';
    $a = Hash::generate( $plain, 'SHA256' );

    $this->assertEquals($hash, $a);
  }

  /**
   * Test UUID v4 generation
   */
  public function testUuidV4()
  {
    $a = UUID::v4();

    $this->assertTrue( strlen($a)>0 );
  }

  /**
   * Test UUID v5 generation
   */
  public function testUuidV5()
  {
    $a = UUID::v5( 'fe590d59-b698-4246-98a0-521e31427ee4', 'Glorius');

    $this->assertEquals('0eeda2f3-b68c-5ae7-a0ab-cc14eac039db', $a);
  }
}