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

class Cipher implements CipherInterface
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
  public static function encrypt(string $data, string $secret = '', string $algorithm='AES-256-CBC'): string
  {
    // # If AES we will add a random 16 byte IV before the encrypted data
    # Add a random Initialization Vector
    $iv = \openssl_random_pseudo_bytes(16);

    # If no secret provided, use the one in config
    if (empty($secret)) {
      $secret = \config()->get('application.secret');
    }

    # Encrypt
    $result = \openssl_encrypt($data, $algorithm, $secret, 0, $iv);

    if (!$result===false) {
      return \base64_encode($iv . $result);
    }

    return '';
  }

  /**
   * Decrypt $data with $secret
   *
   * @param      string  $data
   * @param      string  $secret
   * @param      string  $algorithm
   *
   * @return     bool|string
   */
  public static function decrypt(string $data, string $secret='', string $algorithm='AES-256-CBC'): bool|string
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
    return \openssl_decrypt($encoded,$algorithm,$secret,0,$iv);
  }


  /**
   * Extended encryption with $data & $secret
   *
   * @param string $data
   * @param string $secret
   * @param string $cipher   read more: https://www.php.net/manual/en/function.openssl-get-cipher-methods.php
   * @param string $hashAlgo read more: https://www.php.net/manual/en/function.hash-hmac-algos.php
   * @return string         `cipher[hashAlgo]:base64(iv).base64(encrypted $data).base64(hash)`
   * @throws \Exception
   */
  public static function encryptEx(string $data,
                                   string $secret,
                                   string $cipher = 'aes-256-ctr',
                                   string $hashAlgo = 'sha3-512'): string
  {
    # lowercase cipher & hashAlgo
    $cipher   = \mb_strtolower($cipher);
    $hashAlgo = \mb_strtolower($hashAlgo);

    # check if cipher is supported
    if(!\in_array($cipher, \openssl_get_cipher_methods(), true)) {
      throw new \RuntimeException('Cipher method not supported');
    }
    
    # check if hash algorithm is supported
    if(!\in_array($hashAlgo, \hash_hmac_algos())) {
      throw new \RuntimeException('Hash algorithm not supported');
    }
    
    # check if we have a secret
    if($secret === '') {
      throw new \RuntimeException('Secret is empty');
    }

    # data has to exist
    if($data === '') {
      throw new \RuntimeException('Data is empty');
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
    return $cipher . "[".$hashAlgo."]:" . \base64_encode($iv) . '.' . \base64_encode($encrypted) . '.' . \base64_encode($hash);
  }

  /**
   * Extended Decryption method
   *
   * Decrypts data with $secret, return original input if not encrypted
   *
   * @param string $data   format: `cipher[hashAlgo]:base64(iv).base64(encrypted).base64(hash)`
   * @param string $secret salt
   * @return string|bool   decrypted data or FALSE on failure
   * @throws \Exception
   */
  public static function decryptEx(string $data, string $secret): string|bool
  {
    # check if we have a secret
    if($secret === '') {
      throw new \RuntimeException('Secret is empty');
    }

    # input has to exist
    if($data === '') {
      throw new \RuntimeException('Input is empty');
    }

    # regex pattern
    $pattern = '/([[:graph:]]+)\[([[:graph:]]+)\]/';

    # check for patterns in string
    \preg_match($pattern, $data, $matches);

    # if $input has no matches, return original string
    if (count($matches) < 3) {
      throw new \RuntimeException('Encryption pattern not found');
    }

    # get the whole match cipher and hashAlgo
    $match      = $matches[0];
    $cipher     = \mb_strtolower($matches[1]);
    $hashAlgo   = \mb_strtolower($matches[2]);

    # check if cipher is supported
    if (!\in_array($cipher, \openssl_get_cipher_methods(), true)) {
      throw new \RuntimeException('Cipher method not supported');
    }

    # check if hash algorithm is supported
    if (!\in_array($hashAlgo, \hash_hmac_algos())) {
      throw new \RuntimeException('Hash algorithm not supported');
    }

    # remove the match from the input
    $length             = \mb_strlen($match);
    $encodedString      = \mb_substr($data, $length);
    # create a list of the encoded values
    [$iv, $string, $hash] = \explode('.', $encodedString);

    # decode values
    $iv     = \base64_decode($iv);
    $string = \base64_decode($string);
    $hash   = \base64_decode($hash);

    # try to run decryption
    $payload = \openssl_decrypt($string, $cipher, $secret, 0, $iv);

    # create a new hash
    $hashedData = \hash_hmac($hashAlgo, $payload, $secret, TRUE);

    # if the newly created hash matches old hash, data is valid
    if (!\hash_equals($hash, $hashedData)) {
      # throw error on invalid token
      throw new \RuntimeException('Invalid hash');
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
