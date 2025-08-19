<?php declare(strict_types=1);

/**
 * CipherInterface
 *
 * @package  Spin
 */

namespace Spin\Helpers;

interface CipherInterface
{
  /**
   * Encrypt $data with $secret
   *
   * @param      string  $data
   * @param      string  $secret
   * @param      string  $algorithm
   *
   * @return     string  Base64 Encoded String
   */
  public static function encrypt(string $data, string $secret = '', string $algorithm = 'AES-256-CBC'): string;

  /**
   * Decrypt $data with $secret
   *
   * @param      string  $data
   * @param      string  $secret
   * @param      string  $algorithm
   *
   * @return     string
   */
  public static function decrypt(string $data, string $secret = '', string $algorithm = 'AES-256-CBC'): string;


  /**
   * Extended encryption with $data & $secret
   *
   * @param  string  	          $data
   * @param  string	            $secret
   * @param  string	            $cipher   read more: https://www.php.net/manual/en/function.openssl-get-cipher-methods.php
   * @param  string	            $hashAlgo read more: https://www.php.net/manual/en/function.hash-hmac-algos.php
   * @return string          	  `cipher[hashAlgo]:base64(iv).base64(encrypted $data).base64(hash)`
   */

  public static function encryptEx(string $data,
                                   string $secret,
                                   string $cipher = 'aes-256-ctr',
                                   string $hashAlgo = 'sha3-512'): string;


  /**
   * Extended Decryption method
   *
   * Decrypts data with $secret, return original input if not encrypted
   *
   * @param   string	          $data     format: `cipher[hashAlgo]:base64(iv).base64(encrypted).base64(hash)`
   * @param   string	          $secret   salt
   * @return  mixed          	            decrypted data or FALSE on failure
   */
  public static function decryptEx(string $data, string $secret): mixed;


  /**
   * Return array of Cipher methods available
   *
   * @return     array
   */
  public static function getMethods(): array;

}
