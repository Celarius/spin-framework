<?php declare(strict_types=1);

/**
 * UUID Library
 *
 * @package  Spin
 */

namespace Spin\Helpers;

use \Ramsey\Uuid\Uuid AS RamseyUUID;
use \Spin\Helpers\UUIDInterface;

class UUID implements UUIDInterface
{
  /**
   * Generate v6 UUID
   *
   * @return     string
   */
  public static function generate(): string
  {
    return self::v6();
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
   * Generate a v6 UUID
   *
   * @return     string
   */
  public static function v6(): string
  {
    return RamseyUUID::uuid6()->toString();
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
