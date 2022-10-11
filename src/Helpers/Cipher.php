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
  * @param  mixed  	            $data 
  * @param  string	            $secret 
  * @param int		              $ttl      time to live in seconds, default 30 seconds
  * @param  string	            $cipher   read more: https://www.php.net/manual/en/function.openssl-get-cipher-methods.php
  * @param  string	            $hashAlgo read more: https://www.php.net/manual/en/function.hash-hmac-algos.php
  * @return string|Exception	  `cipher[hashAlgo]:base64(iv).base64(encrypted $data).base64(hash)`
  */ 

  public static function encryptEx(mixed $data, string $secret, int $ttl = 30, string $cipher='aes-256-ctr', string $hashAlgo='sha3-512')
  {
    # check if cipher is supported
    if(!\in_array($cipher, \openssl_get_cipher_methods())) {
      throw new \Exception('Cipher method not supported');
    }
    
    # check if hash algorithm is supported
    if(!\in_array($hashAlgo, \hash_hmac_algos())) {
      throw new \Exception('Hash algorithm not supported');
    }
    
    # check if we have a secret
    if(empty($secret)) {
      throw new \Exception('Secret is empty');
    }
    
    # data has to exist
    if(empty($data)) {
      throw new \Exception('No data provided');
    }

    # create a base data model with timestamp
    $input = \json_encode(["data" => $data, "expires_dt" => (new \DateTime('now'))->getTimestamp() + $ttl]);
    
    # get cipher iv length
    $iv_length = \openssl_cipher_iv_length($cipher);

    # create a random initialization vector 
    $iv = \openssl_random_pseudo_bytes($iv_length);
    
    # create encryption
    $encrypted = \openssl_encrypt($input,$cipher,$secret, 0 ,$iv);

    # create a hash from the data
    $hash = \hash_hmac($hashAlgo, $input, $secret, TRUE);
              
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
    # regex pattern
    $pattern = '/([[:graph:]]+)\[([[:graph:]]+)\]/';

    # check for patterns in string
    \preg_match($pattern, $input, $matches);

    # if $input has no matches, return original string
    if (count($matches) < 3) {
      return $input;
    }

    # get the whole match cipher and hashAlgo
    $match      = $matches[0];
    $cipher     = $matches[1];
    $hashAlgo   = $matches[2];

    # check if cipher is supported
    if (!\in_array($cipher, \openssl_get_cipher_methods())) {
      throw new \Exception('Cipher method not supported');
    }

    # check if hash algorithm is supported
    if (!\in_array($hashAlgo, \hash_hmac_algos())) {
      throw new \Exception('Hash algorithm not supported');
    }

    # remove the match from the input
    $length             = \strlen($match);
    $encodedString      = \substr($input, $length);
    $mix                = \explode('.', $encodedString);

    # create a list of the encoded values
    list($iv, $string, $hash) = $mix;

    # decode values
    $iv     = \base64_decode($iv);
    $string = \base64_decode($string);
    $hash   = \base64_decode($hash);

    # try to run decryption
    try {

      $payload = \openssl_decrypt($string, $cipher, $secret, 0, $iv);

      # create a new hash
      $hashedData = \hash_hmac($hashAlgo, $payload, $secret, TRUE);

      # if the newly created hash matches old hash, data is valid
      if (\hash_equals($hash, $hashedData)) {
        # decode payload
        $json = \json_decode($payload, true);

        # get expires date
        $expires_dt = $json['expires_dt'];

        # check if timestamp is older than a ttl
        if ($expires_dt < (new \DateTime('now'))->getTimestamp()) {
          # throw error on invalid token
          throw new \Exception('Encryption has expired');
        }

        # payload data
        $data = $json["data"];

      } else {
        # throw error on invalid token
        throw new \Exception('Invalid hash');
      }
    } catch (\Exception $e) {
      # throw error on decryption failure
      throw $e;
    }

    return $data;
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
