<?php declare(strict_types=1);

/**
 * Spin\Helper\UUID
 *
 *   Generates a v4 UUID
 *
 * Exmaple:
 *   $uuidv4 = \\Spin\\Helper\\UUID::generate();                  // v4 UUID
 *   $uuidv5 = \\Spin\\Helper\\UUID::v5($uuidv4,'My v5 UUID');    // v5 UUID
 *
 * @package  Spin
 */

namespace Spin\Helpers;

use \Spin\Helper\UUIDInterface;

class UUID implements UUIDInterface
{
  /**
   * Generate v4 UUID
   *
   * @return string
   */
  public static function generate(): string
  {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
  }

  /**
   * Generate v4 UUID
   *
   * @return string
   */
  public static function v4(): string
  {
    return self::generate();
  }

  /**
   * Generate a v5 UUID, based on $namespace and $name
   *
   * @param  string $namespace    A Valid UUID
   * @param  string $name         A Random String
   * @return string
   */
  public static function v5(string $namespace, string $name): string
  {
    if(!self::is_valid($namespace)) return false;

    // Get hexadecimal components of namespace
    $nhex = str_replace(array('-','{','}'), '', $namespace);

    // Binary Value
    $nstr = '';

    // Convert Namespace UUID to bits
    for($i = 0; $i < strlen($nhex); $i+=2) {
      $nstr .= chr(hexdec($nhex[$i].$nhex[$i+1]));
    }

    // Calculate hash value
    $hash = sha1($nstr . $name);

    return
      sprintf('%08s-%04s-%04x-%04x-%12s',
      // 32 bits for "time_low"
      substr($hash, 0, 8),
      // 16 bits for "time_mid"
      substr($hash, 8, 4),
      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 5
      (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,
      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
      // 48 bits for "node"
      substr($hash, 20, 12)
    );
  }

  /**
   * Checks if an UUID is valid (v3,v4 and v5)
   *
   * @param  string  $uuid
   * @return bool
   */
  public static function is_valid(string $uuid): bool
  {
    return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?'.
                      '[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
  }
}
