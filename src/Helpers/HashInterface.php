<?php declare(strict_types=1);

/**
 * HashInterface
 *
 * @package  Spin
 */

namespace Spin\Helpers;

interface HashInterface
{
  /**
   * Genreate a Hash (digest) of the $data using $method
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
