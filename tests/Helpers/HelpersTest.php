<?php declare(strict_types=1);

namespace Spin\tests\Helpers;

use PHPUnit\Framework\TestCase;

use \Spin\Helpers\Cipher;
use \Spin\Helpers\Hash;
use \Spin\Helpers\UUID;
use \Spin\Helpers\JWT;
use \Spin\Helpers\EWT;

class HelpersTest extends TestCase
{
  /** @var  string                    Application object */
  protected $app;

  /** @var  string                    String used for encryption */
  protected $secret;

  /**
   * Setup test
   */
  public function setUp(): void
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
  * Test OpenSSL Encryption / Decryption
  */
  public function testExtendedCipher()
  {
    $plain = 'Let this be the light';
    $encrypted = Cipher::encryptEx( $plain, $this->secret );
    $a = Cipher::decryptEx( $encrypted, $this->secret );

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

    $this->assertTrue( \mb_strlen($a)>0 );
  }

  /**
   * Test UUID v5 generation
   */
  public function testUuidV5()
  {
    $a = UUID::v5( 'fe590d59-b698-4246-98a0-521e31427ee4', 'Glorius');

    $this->assertEquals('0eeda2f3-b68c-5ae7-a0ab-cc14eac039db', $a);
  }

  /**
   * Test JWT Encoding
   *
   * @covers \EWT
   */
  public function testJwtEncodeDecode()
  {
    $jwt = JWT::encode(['abc123'],'xyz987');
    $payload = JWT::decode($jwt,'xyz987',['HS256']);

    $this->assertEquals($payload,['abc123']);
  }

  /**
   * Test EWT Encoding
   *
   * @covers \EWT
   */
  public function testEwtEncodeDecode()
  {
    $ewt = EWT::encode('abc123',\config('application.secret') ?? 'xyz987');
    $payload = EWT::decode($ewt,\config('application.secret') ?? 'xyz987');

    $this->assertEquals($payload,'abc123');
  }

  /**
   * testUuidVersion3
   */
  public function testUuidVersion3()
  {
    # version-3
    $a = UUID::is_uuid_valid("0e3b156d-a0fe-35a5-a54f-ede569c67c46");

    # assertion
    $this->assertTrue($a);
  }

  /**
   * testUuidVersion4
   */
  public function testUuidVersion4()
  {
    # version-4
    $a = UUID::is_uuid_valid("14a7ed96-88c8-44cd-b054-70e675a5636a");

    # assertion
    $this->assertTrue($a);
  }

  /**
   * testUuidVersion5
   */
  public function testUuidVersion5()
  {
    # version-5
    $a = UUID::is_uuid_valid("d3ddb111-41d0-5baf-96de-301a2dda8272");

    # assertion
    $this->assertTrue($a);
  }

  /**
   * testUuidVersion6
   */
  public function testUuidVersion6()
  {
    # version-6
    $a = UUID::is_uuid_valid("1ed5c346-a174-6370-9b32-00090faa0001");

    # assertion
    $this->assertTrue($a);
  }

}
