<?php declare(strict_types=1);

/**
 * JWTInterface
 *
 * @package  Spin
 */

namespace Spin\Helpers;

interface JWTInterface
{
  /**
   * Decodes a JWT string into a PHP object.
   *
   * @param      string        $jwt           The JWT
   * @param      string|array  $key           The key, or map of keys. If the
   *                                          algorithm used is asymmetric, this
   *                                          is the public key
   * @param      array         $allowed_algs  List of supported verification
   *                                          algorithms Supported algorithms
   *                                          are 'HS256', 'HS384', 'HS512' and
   *                                          'RS256'
   *
   * @return     object  The JWT's payload as a PHP object
   * 
   * @throws     \Exception                   Provided JWT was invalid
   * 
   * @uses       jsonDecode
   * @uses       urlsafeB64Decode
   */
  static function decode($jwt, $key, array $allowed_algs = array());

  /**
   * Converts and signs a PHP object or array into a JWT string.
   *
   * @param      object|array  $payload  PHP object or array
   * @param      string        $key      The secret key. If the algorithm used
   *                                     is asymmetric, this is the private key
   * @param      string        $alg      The signing algorithm. Supported
   *                                     algorithms are 'HS256', 'HS384',
   *                                     'HS512' and 'RS256'
   * @param      mixed         $keyId
   * @param      array         $head     An array with header elements to attach
   *
   * @return     string  A signed JWT
   * @uses       jsonEncode
   * @uses       urlsafeB64Encode
   */
  static function encode($payload, $key, $alg = 'HS256', $keyId = null, $head = null);

  /**
   * Sign a string with a given key and algorithm.
   *
   * @param      string           $msg    The message to sign
   * @param      string|resource  $key    The secret key
   * @param      string           $alg    The signing algorithm. Supported
   *                                      algorithms are 'HS256', 'HS384',
   *                                      'HS512' and 'RS256'
   * @return     string  An encrypted message
   * @throws     DomainException  Unsupported algorithm was specified
   */
  static function sign($msg, $key, $alg = 'HS256');

  /**
   * Decode a JSON string into a PHP object.
   *
   * @param      string  $input  JSON string
   *
   * @return     object  Object representation of JSON string
   * @throws     DomainException  Provided string was invalid JSON
   */
  static function jsonDecode($input);

  /**
   * Encode a PHP object into a JSON string.
   *
   * @param      object|array  $input  A PHP object or array
   *
   * @return     string  JSON representation of the PHP object or array
   * @throws     DomainException  Provided object could not be encoded to valid JSON
   */
  static function jsonEncode($input);

  /**
   * Decode a string with URL-safe Base64.
   *
   * @param      string  $input  A Base64 encoded string
   *
   * @return     string  A decoded string
   */
  static function urlsafeB64Decode($input);

  /**
   * Encode a string with URL-safe Base64.
   *
   * @param      string  $input  The string you want encoded
   *
   * @return     string  The base64 encode of what you passed in
   */
  static function urlsafeB64Encode($input);

 }
