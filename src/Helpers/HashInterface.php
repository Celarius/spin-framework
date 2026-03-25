<?php declare(strict_types=1);

/**
 * Cryptographic Hash Interface
 *
 * Defines the contract for hash operations including generation, verification,
 * and method listing. Provides a clean interface for working with cryptographic
 * hashes in the Spin framework.
 *
 * @package  Spin\Helpers
 * @author   Spin Framework Team
 * @since    1.0.0
 */

namespace Spin\Helpers;

interface HashInterface
{
  /**
   * Generate a Hash (digest) of the $data using $method
   *
   * @param      string  $data    [description]
   * @param      string  $method  [description]
   *
   * @return     string
   */
  public static function generate(string $data, string $method='SHA256'): string;

  /**
   * Check that the $hash of $data is correct, using $method
   *
   * @param      string  $data    [description]
   * @param      string  $hash    [description]
   * @param      string  $method  [description]
   *
   * @return     bool
   */
  public static function check(string $data, string $hash, string $method='SHA256'): bool;

  /**
   * Return array of hash Methods available
   *
   * @return     array
   */
  public static function getMethods(): array;
}
