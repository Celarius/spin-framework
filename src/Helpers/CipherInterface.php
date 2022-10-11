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
   * @param      string  $data       [description]
   * @param      string  $secret     [description]
   * @param      string  $algorithm  [description]
   *
   * @return     string  Base64 Encoded String
   */
  public static function encrypt(string $data, string $secret='', string $algorithm='AES-256-CBC');

  /**
   * Decrypt $data with $secret
   *
   * @param      string  $data       [description]
   * @param      string  $secret     [description]
   * @param      string  $algorithm  [description]
   *
   * @return     string
   */
  public static function decrypt(string $data, string $secret='', string $algorithm='AES-256-CBC');


 /**
  * Extended encryption with $data & $secret
  *
  * @param  mixed  	            $data 
  * @param  string	            $secret 
  * @param  string	            $cipher   read more: https://www.php.net/manual/en/function.openssl-get-cipher-methods.php
  * @param  string	            $hashAlgo read more: https://www.php.net/manual/en/function.hash-hmac-algos.php
  * @return string|Exception	  `cipher[hashAlgo]:base64(iv).base64(encrypted $data).base64(hash)`
  */ 

  public static function encryptEx(mixed $data, string $secret, string $cipher='aes-256-ctr', string $hashAlgo='sha3-512');

  
 /**
  * Extended Decryption method
  * 
  * Decrypts data with $secret, implements dynamic time to live
  * 
  * @param string	          $input    format: `cipher[hashAlgo]:base64(iv).base64(encrypted).base64(hash)`
  * @param string	          $secret   salt
  * @param int		          $ttl      time to live default 30 seconds
  * @return mixed|Exception	          decrypted data or FALSE on failure
  */

  public static function decryptEx(string $input, string $secret, int $ttl = 30);

  /**
   * Return array of Cipher methods available
   *
   * @return     array
   */
  public static function getMethods(): array;

}
