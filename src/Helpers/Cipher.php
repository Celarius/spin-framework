<?php declare(strict_types=1);

/**
 * Cipher
 *
 *   Wraps the OpenSSL encrypt() and decrypt() methods into easily usable static helper methods
 *   Note: Uses the Configuration setting "application.secret" as the default password.
 *
 * Example:
 *   $encryptedValue = \Spin\Helper\Cipher::encrypt( $plain );
 *   $plain = \Spin\Helper\Cipher::decrypt( $encryptedValue );
 *
 * @package  Spin
 */

namespace Spin\Helpers;

use \Spin\Helpers\CipherInterface;

class Cipher implements CipherInterface
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
  public static function encrypt(string $data, string $secret='', string $algorithm='AES-256-CBC')
  {
    // # If AES we will add a random 16 byte IV before the encrypted data
    // if ( strtoupper(substr($algorithm,0,3))==='AES' ) {
    //   $iv = openssl_random_pseudo_bytes(16);
    // } else {
    //   $iv = '';
    // }
    # Add a random Initialization Vector
    $iv = \openssl_random_pseudo_bytes(16);

    # If no secret provided, use the one in config
    if (empty($secret))
      $secret = \config()->get('application.secret');

    # Encrypt
    $result = \openssl_encrypt($data,$algorithm,$secret,0,$iv);

    if ( !$result===false ) {
      return \base64_encode($iv.$result);
    } else {
      return '';
    }

  }

  /**
   * Decrypt $data with $secret
   *
   * @param      string  $data       [description]
   * @param      string  $secret     [description]
   * @param      string  $algorithm  [description]
   *
   * @return     bool|string
   */
  public static function decrypt(string $data, string $secret='', string $algorithm='AES-256-CBC')
  {
    # if AES we extract the 16 bytes in the beginning as the IV
    if ( \strtoupper(\substr($algorithm,0,3))==='AES' ) {
      $encoded  = \base64_decode($data);
      $iv       = \substr($encoded,0,16);
      $encoded  = \substr($encoded,16);
    } else {
      $encoded = $data;
      $iv = '';
    }

    # If no secret provided, use the one in config
    if (empty($secret))
      $secret = \config()->get('application.secret');

    # Decrypt
    $result = \openssl_decrypt($encoded,$algorithm,$secret,0,$iv);

    return $result;
  }

  
  /**
  * Extended encryption with $data & $secret
  *
  * @param  string  	          $data 
  * @param  string	            $secret 
  * @param  string	            $cipher   read more: https://www.php.net/manual/en/function.openssl-get-cipher-methods.php
  * @param  string	            $hashAlgo read more: https://www.php.net/manual/en/function.hash-hmac-algos.php
  * @return string|Exception	  `cipher[hashAlgo]:base64(iv).base64(encrypted $data).base64(hash)`
  */ 

  public static function encryptEx(string $data, string $secret, string $cipher='aes-256-ctr', string $hashAlgo='sha3-512')
  {
    # lowercase cipher & hashAlgo
    $cipher   = \mb_strtolower($cipher);
    $hashAlgo = \mb_strtolower($hashAlgo);

    # check if cipher is supported
    if(!\in_array($cipher, \openssl_get_cipher_methods())) {
      throw new \Exception('Cipher method not supported');
    }
    
    # check if hash algorithm is supported
    if(!\in_array($hashAlgo, \hash_hmac_algos())) {
      throw new \Exception('Hash algorithm not supported');
    }
    
    # check if we have a secret
    if(\mb_strlen($secret ?? '') == 0) {
      throw new \Exception('Secret is empty');
    }

    # data has to exist
    if(\mb_strlen($data ?? '') == 0) {
      throw new \Exception('Data is empty');
    }

    # get cipher iv length
    $iv_length = \openssl_cipher_iv_length($cipher);

    # create a random initialization vector 
    $iv = \openssl_random_pseudo_bytes($iv_length);
    
    # create encryption
    $encrypted = \openssl_encrypt($data, $cipher, $secret, 0, $iv);

    # create a hash from the data
    $hash = \hash_hmac($hashAlgo, $data, $secret, TRUE);
              
    # data in format ciper[hashAlgo]:base64(iv).base64(encrypted).base64(hash)
    $output = $cipher . "[".$hashAlgo."]:" . \base64_encode($iv) . '.' . \base64_encode($encrypted) . '.' . \base64_encode($hash);

    return $output;
  }

/**
 * Extended Decryption method
 * 
 * Decrypts data with $secret, return original input if not encrypted
 * 
 * @param   string	          $input    format: `cipher[hashAlgo]:base64(iv).base64(encrypted).base64(hash)`
 * @param   string	          $secret   salt
 * @return  mixed|Exception	            decrypted data or FALSE on failure
 */

  public static function decryptEx(string $input, string $secret)
  {
    # check if we have a secret
    if(\mb_strlen($secret ?? '') == 0) {
      throw new \Exception('Secret is empty');
    }

    # input has to exist
    if(\mb_strlen($input ?? '') == 0) {
      throw new \Exception('Input is empty');
    }

    # regex pattern
    $pattern = '/([[:graph:]]+)\[([[:graph:]]+)\]/';

    # check for patterns in string
    \preg_match($pattern, $input, $matches);

    # if $input has no matches, return original string
    if (count($matches) < 3) {
      throw new \Exception('Encryption pattern not found');
    }

    # get the whole match cipher and hashAlgo
    $match      = $matches[0];
    $cipher     = \mb_strtolower($matches[1]);
    $hashAlgo   = \mb_strtolower($matches[2]);

    # check if cipher is supported
    if (!\in_array($cipher, \openssl_get_cipher_methods())) {
      throw new \Exception('Cipher method not supported');
    }

    # check if hash algorithm is supported
    if (!\in_array($hashAlgo, \hash_hmac_algos())) {
      throw new \Exception('Hash algorithm not supported');
    }

    # remove the match from the input
    $length             = \mb_strlen($match);
    $encodedString      = \mb_substr($input, $length);
    $mix                = \explode('.', $encodedString);

    # create a list of the encoded values
    list($iv, $string, $hash) = $mix;

    # decode values
    $iv     = \base64_decode($iv);
    $string = \base64_decode($string);
    $hash   = \base64_decode($hash);

    try {
      # try to run decryption
      $payload = \openssl_decrypt($string, $cipher, $secret, 0, $iv);

      # create a new hash
      $hashedData = \hash_hmac($hashAlgo, $payload, $secret, TRUE);

      # if the newly created hash matches old hash, data is valid
      if (!\hash_equals($hash, $hashedData)) {
        # throw error on invalid token
        throw new \Exception('Invalid hash');
      }
    } catch (\Exception $e) {
      # throw error on decryption failure
      throw $e;
    }

    return $payload;
  }

  /**
   * Return array of Cipher methods available
   *
   * @return     array
   */
  public static function getMethods(): array
  {
    return \openssl_get_cipher_methods();
  }

}
