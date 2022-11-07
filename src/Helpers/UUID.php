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
  
  /**
   * Checks if an UUID is valid
   *
   * @param   void  $uuid
   *
   * @return  bool  True if valid
   */
  public function is_uuid_valid($uuid): bool
  {
    # make sure uuid is not null & empty
    if (!$uuid || \mb_strlen($uuid) == 0) return false;

    # always compare uuid with lowercase
    $uuid = \strtolower($uuid);

    # validate
    if (\preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-[3-6][0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i', $uuid) !== 1) {
      return false;
    }

    # assume fine
    return true;
  }

}
