<?php declare(strict_types=1);

/**
 * Configuration Management Class
 *
 * Loads, merges, and persists environment-specific JSON configuration for the
 * application. Provides dot-notation accessors and mutation helpers for
 * managing application settings across different environments.
 *
 * @package   Spin
 * @author    Spin Framework Team
 * @since     1.0.0
 */

namespace Spin\Core;

use \Exception;
use \Spin\Exceptions\SpinException;

/**
 * Loads, merges, and persists environment-specific JSON configuration for the
 * application. Provides dot-notation accessors and mutation helpers.
 */
class Config extends AbstractBaseClass implements ConfigInterface
{
  /**
   * Configuration Array
   * @var  array<mixed>
   */
  protected array $confValues = [];

  /**
   * Config file name
   * @var  string
   */
  protected string $filename;


  /**
   * Constructor
   *
   * Load config file based on $appPath and $environment
   *
   * @param   string $appPath             Path to the /app folder
   * @param   string $environment         Name of the environment
   *
   * @throws  Exception
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
   * Destructor
   */
  public function __destruct()
  {
    $this->clear();
  }

  /**
   * Clear all config values
   *
   * @return     self
   */
  public function clear(): self
  {
    $this->confValues = [];

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
        $this->confValues = $this->replaceEnvMacros($configArray);
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
        $this->confValues = $this->replaceEnvMacros(\array_replace_recursive($this->confValues, $configArray));
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

  /**
   * Requirsively replaces `${env:<envVar>}` with the environment variabe <envVar>.
   *
   * Missing environment variables are replaced with an empty string.
   *
   * note: Environment variable names are case-sensitive on Unix-like systems but aren't case-sensitive on Windows
   *
   * @param   array<mixed> $input           Config array to process
   * @param   array<mixed>|null $envVars    Optional Environment variables to use for replacement, KEY=VALUE pairs
   *
   * @return  array<mixed>                  Processed config array
   */
  protected function replaceEnvMacros(array $input, ?array $envVars=null): array
  {
    if ($envVars === null) {
      // Get all env vars into array
      $envVars = array_merge($_ENV, getenv());
    }

    foreach ($input as $key => $value) {
      if (\is_array($value)) {
        # Recurse into sub-array
        $input[$key] = $this->replaceEnvMacros($value, $envVars);
      } elseif (\is_string($value)) {
        # Replace all `${env:<envVar>}` with the environment variable value
        $input[$key] = \preg_replace_callback(
          '/\$\{env:([A-Za-z0-9_]+)\}/',
          function ($matches) use ($envVars) {
            $envVarName = $matches[1];
            return $envVars[$envVarName] ?? '';
          },$value);
      }
    }

    return $input;
  }
}
