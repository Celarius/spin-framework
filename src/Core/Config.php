<?php declare(strict_types=1);

/**
 * Config class
 *
 * @package   Spin
 */

namespace Spin\Core;

use \Exception;
use \Spin\Exceptions\SpinException;

class Config extends AbstractBaseClass implements ConfigInterface
{
  /** @var  array         Configuration Array */
  protected array $confValues = array();

  /** @var  string        Config file name */
  protected string $filename;

  /**
   * Constructor
   *
   * Load config file based on $appPath and $environment
   *
   * @param string $appPath     Path to the /app folder
   * @param string $environment Name of the environment
   * @throws Exception
   */
  public function __construct(string $appPath, string $environment)
  {
    parent::__construct();
    $this->clear();

    # Build $filename based on $appPath and $environment
    $filename = $appPath . \DIRECTORY_SEPARATOR . 'Config' . \DIRECTORY_SEPARATOR . 'config-' . $environment.'.json';

    # Load the config
    $this->load($filename);
  }


  /**
   * Clear all config values
   *
   * @return     self
   */
  public function clear(): self
  {
    $this->confValues = array();

    return $this;
  }

  /**
   * Load Configuration file
   *
   * @param      string     $filename
   *
   * @throws     Exception  On invalid JSON file
   *
   * @return     self
   */
  public function load(string $filename): self
  {
    # Attempt to load config file
    if (\file_exists($filename)) {
      # Set filename
      $this->filename = $filename;

      # Load the config
      try {
        $configArray = \json_decode(\file_get_contents($filename), true, 512, JSON_THROW_ON_ERROR);
      } catch (\JsonException $e) {
        throw new SpinException(sprintf("Invalid JSON file %s, error was %s", $filename, $e->getMessage()));
      }

      if ($configArray) {
        $this->confValues = $configArray;
      } else {
        throw new SpinException('Invalid JSON file "' . $filename . '"');
      }
    }

    return $this;
  }

  /**
   * Load & Merge Configuration file to existing config
   *
   * @param      string     $filename
   *
   * @throws     Exception  On invalid JSON file
   *
   * @return     self
   */
  public function loadAndMerge(string $filename): self
  {
    # Attempt to load config file
    if ( \file_exists($filename) ) {
      # Set filename
      $this->filename = $filename;
      # Load the config
      try {
        $configArray = \json_decode(\file_get_contents($filename), true, 512, JSON_THROW_ON_ERROR);
      } catch (\JsonException $e) {
        throw new SpinException(sprintf("Invalid JSON file %s, error was %s", $filename, $e->getMessage()));
      }

      if ($configArray) {
        # Merge the Config with existing config
        $this->confValues = \array_replace_recursive($this->confValues, $configArray);
      } else {
        throw new SpinException('Invalid JSON file "' . $filename . '"');
      }
    }

    return $this;
  }

  /**
   * Save Configuration file
   *
   * @param      string  $filename  If null the last used filename is used
   *
   * @return     bool
   */
  function save(string $filename = ''): bool
  {
    if (!empty($filename)) {
      $this->filename = $filename;
    }

    try {
      $content = \json_encode($this->confValues, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

      return (\file_put_contents($this->filename, $content) !== false);
    } catch (\JsonException) {
      // Skip logging since might not be instantiated at this point
      return false;
    }
  }

  /**
   * Get a config item
   *
   * The $key is in DOT format.
   *
   * Example: get('application.code')
   *
   * @param      string  $key      "." notation key to retrieve
   * @param      mixed   $default  Optional Default value if group::section::key
   *                               not found
   *
   * @return     mixed
   */
  public function get(string $key, $default = null): mixed
  {
    $keys = \explode('.',$key);
    $val = $this->confValues;

    foreach ($keys as $value) {
      $val = ($val[$value] ?? null);
      if (\is_null($val)) {
        break;
      }
    }

    return $val ?? $default;
  }

  /**
   * Set a Configuration $key to $value
   *
   * The $key is in DOT format.
   *
   * Example: set('application.code','theValue');
   *
   * @param      string  $key Key to update/set. Dot notation
   * @param      mixed   $value
   *
   * @return     self
   */
  public function set(string $key, mixed $value): self
  {
    $keys = \explode('.', $key);
    $arr = &$this->confValues;
    $arrParent = null;

    # Walk the structure
    $lastKey = null;
    foreach ($keys as $internalKey) {
      $arrParent = &$arr;
      $lastKey = $internalKey;
      if (!isset($arr[$internalKey])) {
        $arr[$internalKey] = [];
      }
      $arr = &$arr[$internalKey];
    }

    if (\is_null($value)) {
      if ($lastKey !== null) {
        unset($arrParent[$lastKey]); // delete the key
      }
    } else {
      $arr = $value; // set the value in the original confArray
    }

    return $this;
  }

  /**
   * Get config filename
   *
   * @return     string
   */
  public function getFilename(): string
  {
    return $this->filename;
  }

  /**
   * Return all config values
   *
   * @return     array
   */
  public function getValues(): array
  {
    return $this->confValues;
  }

  /**
   * Recursively change the key names of array and subarrays to $case
   *
   * @param      array $input         The array to change
   * @param      int  $case           Case to use CASE_LOWER or CASE_UPPER
   *
   * @return     array                The resulting array
   */
  protected function array_change_key_case_recursive(array $input, int $case = \CASE_LOWER): array
  {
    # Check the CASE param
    if (!in_array($case, [\CASE_UPPER, \CASE_LOWER], true)) {
      return [];
    }

    # Initial Key Case Change for root level keys
    $input = \array_change_key_case($input, $case);

    # Loop all keys in all sub-arrays
    foreach($input as $key => $array) {
      if (\is_array($array)) {
        $input[$key] = $this->array_change_key_case_recursive($array, $case);
      }
    }

    return $input;
  }

}
