<?php declare(strict_types=1);

/**
 * Spin\Helper\CipherInterface
 *
 * @package  Spin
 */

namespace Spin\Helpers;

interface CipherInterface
{
  /**
   * Encrypt $data with $secret
   *
   * @param  string $data        [description]
   * @param  string $iv          [description]
   * @param  string $secret      [description]
   * @param  string $algorithm   [description]
   * @return string              Base64 Encoded String
   */
  public static function encrypt(string $data, string $secret='', string $algorithm='AES-256-CBC');

  /**
   * Decrypt $data with $secret
   *
   * @param  string $data        [description]
   * @param  string $iv          [description]
   * @param  string $secret      [description]
   * @param  string $algorithm   [description]
   * @return string
   */
  public static function decrypt(string $data, string $secret='', string $algorithm='AES-256-CBC');

  /**
   * Return array of Cipher methods available
   *
   * @return array
   */
  public static function getMethods(): array;

}
