<?php declare(strict_types=1);

/**
 * Universally Unique Identifier (UUID) Interface
 *
 * Defines the contract for UUID operations including generation of different
 * UUID versions and validation. Provides a clean interface for working with
 * UUIDs in the Spin framework.
 *
 * @package  Spin\Helpers
 * @author   Spin Framework Team
 * @since    1.0.0
 */

namespace Spin\Helpers;

interface UUIDInterface
{
  /**
   * Generate v7 UUID
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
   * Generate a v6 UUID, based on time
   *
   * @return     string
   */
  public static function v6(): string;

    /**
   * Generate a v7 UUID, based on time
   *
   * @return     string
   */
  public static function v7(): string;

  /**
   * Checks if an UUID is valid (v3,v4 and v5)
   *
   * @param      string  $uuid
   *
   * @return     bool
   */
  public static function is_valid(string $uuid): bool;

}
