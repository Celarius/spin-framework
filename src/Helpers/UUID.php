<?php declare(strict_types=1);

/**
 * Universally Unique Identifier (UUID) Helper Class
 *
 * Provides UUID generation and validation capabilities using the Ramsey UUID library.
 * Supports multiple UUID versions (v3, v4, v5, v6, v7) with v7 as the default
 * for time-based UUIDs. Includes validation methods for UUID format checking.
 *
 * @package  Spin\Helpers
 * @author   Spin Framework Team
 * @since    1.0.0
 */

namespace Spin\Helpers;

use \Ramsey\Uuid\Uuid AS RamseyUUID;
use \Spin\Helpers\UUIDInterface;

class UUID implements UUIDInterface
{
  /**
   * Generate v7 UUID
   *
   * @return     string
   */
  public static function generate(): string
  {
    return self::v7();
  }

  /**
   * Generate v3 UUID
   *
   * @param      string  $namespace  A Valid UUID
   * @param      string  $name       A Random String
   *
   * @return     string
   */
  public static function v3(string $namespace, string $name): string
  {
    return RamseyUUID::uuid3($namespace,$name)->toString();
  }

  /**
   * Generate v4 UUID
   *
   * @return     string
   */
  public static function v4(): string
  {
    return RamseyUUID::uuid4()->toString();
  }

  /**
   * Generate a v5 UUID, based on $namespace and $name
   *
   * @param      string  $namespace  A Valid UUID
   * @param      string  $name       A Random String
   *
   * @return     string
   */
  public static function v5(string $namespace, string $name): string
  {
    return RamseyUUID::uuid5($namespace,$name)->toString();
  }

  /**
   * Generate a v6 UUID, based on time
   *
   * @return     string
   */
  public static function v6(): string
  {
    return RamseyUUID::uuid6()->toString();
  }

  /**
   * Generate a v7 UUID, based on time
   *
   * @return     string
   */
  public static function v7(): string
  {
    return RamseyUUID::uuid7()->toString();
  }

  /**
   * Checks if an UUID is valid
   *
   * @param      string   $uuid
   *
   * @return     bool     True if valid
   */
  public static function is_valid(string $uuid): bool
  {
    return RamseyUUID::isValid($uuid);
  }
}
