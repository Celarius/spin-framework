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
   * @param string                 $jwt             The JWT
   * @param string                 $key             The Key or associative array of key IDs
   *                                                kid) to Key objects.
   *                                                If the algorithm used is asymmetric, this is
   *                                                the public key.
   *                                                Each Key object contains an algorithm and
   *                                                matching key.
   * @param string                 $algo            Supported algorithms are 'ES384','ES256',
   *                                                HS256', 'HS384', 'HS512', 'RS256', 'RS384'
   *                                                and 'RS512'.
   *
   * @return array                                  The JWT's payload as a PHP object
   *
   * @throws \Exception                             On errors
   */
  public static function decode(
    string $jwt,
    $key,
    $algo = 'HS256'
  );

  /**
   * Converts and signs a PHP array into a JWT string.
   *
   * @param array<mixed>          $payload PHP array
   * @param string|resource|\OpenSSLAsymmetricKey|\OpenSSLCertificate $key The secret key.
   * @param string                $alg     Supported algorithms are 'ES384','ES256', 'ES256K', 'HS256',
   *                                       'HS384', 'HS512', 'RS256', 'RS384', and 'RS512'
   * @param string                $keyId
   * @param array<string, string> $head    An array with header elements to attach
   *
   * @return string A signed JWT
   *
   * @uses jsonEncode
   * @uses urlsafeB64Encode
   */
  public static function encode(
    array $payload,
    $key,
    string $alg,
    string $keyId = null,
    array $head = null
  ): string;

  /**
   * Sign a string with a given key and algorithm.
   *
   * @param string $msg  The message to sign
   * @param string|resource|\OpenSSLAsymmetricKey|\OpenSSLCertificate  $key  The secret key.
   * @param string $alg  Supported algorithms are 'EdDSA', 'ES384', 'ES256', 'ES256K', 'HS256',
   *                    'HS384', 'HS512', 'RS256', 'RS384', and 'RS512'
   *
   * @return string An encrypted message
   *
   * @throws \DomainException Unsupported algorithm or bad key was specified
   */
  public static function sign(
    string $msg,
    $key,
    string $alg
  ): string;

  /**
   * Decode a JSON string into a PHP object.
   *
   * @param string $input JSON string
   *
   * @return mixed The decoded JSON string
   *
   * @throws \DomainException Provided string was invalid JSON
   */
  public static function jsonDecode(string $input);

  /**
   * Encode a PHP array into a JSON string.
   *
   * @param array<mixed> $input A PHP array
   *
   * @return string JSON representation of the PHP array
   *
   * @throws \DomainException Provided object could not be encoded to valid JSON
   */
  public static function jsonEncode(array $input): string;

  /**
   * Decode a string with URL-safe Base64.
   *
   * @param string $input A Base64 encoded string
   *
   * @return string A decoded string
   *
   * @throws \InvalidArgumentException invalid base64 characters
   */
  public static function urlsafeB64Decode(string $input): string;

  /**
   * Encode a string with URL-safe Base64.
   *
   * @param string $input The string you want encoded
   *
   * @return string The base64 encode of what you passed in
   */
  public static function urlsafeB64Encode(string $input): string;

 }
