<?php declare(strict_types=1);

/**
 * Encryption and Decryption Interface
 *
 * Defines the contract for cryptographic operations including basic and extended
 * encryption/decryption methods. Provides a clean interface for working with
 * encryption in the Spin framework.
 *
 * @package  Spin\Helpers
 * @author   Spin Framework Team
 * @since    1.0.0
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
  * @param  string  	          $data 
  * @param  string	            $secret 
  * @param  string	            $cipher   read more: https://www.php.net/manual/en/function.openssl-get-cipher-methods.php
  * @param  string	            $hashAlgo read more: https://www.php.net/manual/en/function.hash-hmac-algos.php
  * @return string|Exception	  `cipher[hashAlgo]:base64(iv).base64(encrypted $data).base64(hash)`
  */ 

  public static function encryptEx(string $data, string $secret, string $cipher='aes-256-ctr', string $hashAlgo='sha3-512');

  
/**
 * Extended Decryption method
 * 
 * Decrypts data with $secret, return original input if not encrypted
 * 
 * @param   string	          $data     format: `cipher[hashAlgo]:base64(iv).base64(encrypted).base64(hash)`
 * @param   string	          $secret   salt
 * @return  mixed|Exception	            decrypted data or FALSE on failure
 */

  public static function decryptEx(string $data, string $secret);


  /**
   * Return array of Cipher methods available
   *
   * @return     array
   */
  public static function getMethods(): array;

}
