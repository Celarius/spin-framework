<?php declare(strict_types=1);

/**
 * UUIDInterface
 *
 * @package  Spin
 */

namespace Spin\Helpers;

interface UUIDInterface
{
  /**
   * Generate v4 UUID
   *
   * @return     string
   */
  public static function generate(): string;

  /**
   * Generate v4 UUID
   *
   * @return     string
   */
  public static function v4(): string;

  /**
   * Generate a v5 UUID, based on $namespace and $name
   *
   * @param      string  $namespace  A Valid UUID
   * @param      string  $name       A Random String
   *
   * @return     string
   */
  public static function v5(string $namespace, string $name): string;

  /**
   * Checks if an UUID is valid (v3,v4 and v5)
   *
   * @param      string  $uuid
   *
   * @return     bool
   */
  public static function is_valid(string $uuid): bool;

}
