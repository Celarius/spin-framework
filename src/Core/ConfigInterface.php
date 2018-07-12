<?php declare(strict_types=1);

/**
 * ConfigInterface
 *
 * @package   Spin
 */

namespace Spin\Core;

interface ConfigInterface
{
  /**
   * Clear all config values
   *
   * @return  self
   */
  function clear();

  /**
   * Load Configuration file
   *
   * @param      string  $filename
   *
   * @return     self
   */
  function load(string $filename);

  /**
   * Load & Merge Configuration file to existing config
   *
   * @param      string  $filename
   *
   * @return     self
   */
  function loadAndMerge(string $filename);

  /**
   * Save Configuration file
   *
   * @param      string  $filename
   *
   * @return     self
   */
  function save(string $filename=null): bool;

  /**
   * Get a config item
   *
   * @param      string  $key      "." notationed key to retreive
   * @param      mied    $default  Optional Default value if group::section::key
   *                               not found
   *
   * @return     mixed
   */
  function get(string $key, $default=null);

  /**
   * Set a config item
   *
   * @param      string  $key    "." notationed key to retreive
   * @param      mixed   $value  Value to set
   *
   * @return     self
   */
  function set(string $key, $value);

  /**
   * Get config filename
   *
   * @return     array
   */
  function getFilename(): string;

  /**
   * Return all config values
   *
   * @return     array
   */
  function getValues(): array;

}
