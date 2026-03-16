<?php declare(strict_types=1);

/**
 * DotEnv — .env file loader
 *
 * Reads a `.env` file from the project root and populates environment
 * variables via putenv() / $_ENV before any config or env() calls run.
 *
 * Priority rule: real process environment always wins — by default, variables
 * already present in the environment are never overwritten. Pass
 * $overwrite = true to change this behaviour (testing, local dev only).
 *
 * @package   Spin
 * @author    Spin Framework Team
 * @since     0.0.39
 */

namespace Spin\Core;

/**
 * Loads a `.env` file and populates process environment variables.
 */
class DotEnv
{
  /**
   * Load a `.env` file from $basePath and populate environment variables.
   *
   * @param   string  $basePath          Directory that contains the .env file (project root)
   * @param   bool    $overwrite         When true, existing env vars are replaced; default false
   *
   * @return  int                        Number of variables actually set
   */
  public static function load(string $basePath, bool $overwrite = false): int
  {
    $file = \rtrim($basePath, '/\\') . \DIRECTORY_SEPARATOR . '.env';

    if (!\file_exists($file) || !\is_readable($file)) {
      return 0;
    }

    $content = \file_get_contents($file);

    if ($content === false) {
      return 0;
    }

    $vars = static::parse($content);
    $count = 0;

    foreach ($vars as $key => $value) {
      if (!$overwrite && \getenv($key) !== false) {
        # Variable already set in the real environment — skip
        continue;
      }

      \putenv("{$key}={$value}");
      $_ENV[$key] = $value;
      $count++;
    }

    return $count;
  }

  /**
   * Parse raw .env file content into a key→value array.
   *
   * Supported formats:
   *   KEY=VALUE            unquoted, trims whitespace
   *   KEY="VALUE"          double-quoted (backslash sequences: \n \t \\ \")
   *   KEY='VALUE'          single-quoted, literal (no escape processing)
   *   export KEY=VALUE     leading `export` keyword is stripped
   *   # comment            lines starting with # are skipped
   *   VAR=value  # inline  text after unquoted # (preceded by whitespace) is stripped
   *
   * @param   string         $content   Raw .env file content
   *
   * @return  array<string, string>     Parsed key→value pairs
   */
  public static function parse(string $content): array
  {
    $vars = [];

    foreach (\explode("\n", $content) as $line) {
      $line = \rtrim($line);

      # Skip blank lines and full-line comments
      if ($line === '' || \str_starts_with(\ltrim($line), '#')) {
        continue;
      }

      # Strip optional leading `export ` keyword
      if (\preg_match('/^export\s+/', $line)) {
        $line = \preg_replace('/^export\s+/', '', $line);
      }

      # Must contain an = sign
      $eqPos = \strpos($line, '=');
      if ($eqPos === false) {
        continue;
      }

      $key   = \trim(\substr($line, 0, $eqPos));
      $value = \substr($line, $eqPos + 1);

      # Key must be a valid identifier
      if (!\preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
        continue;
      }

      $vars[$key] = static::parseValue($value);
    }

    return $vars;
  }

  /**
   * Parse a raw value string, handling quoting and inline comments.
   *
   * @param   string  $raw   Raw value portion (after the = sign)
   *
   * @return  string         Resolved value
   */
  protected static function parseValue(string $raw): string
  {
    $raw = \ltrim($raw);

    if ($raw === '') {
      return '';
    }

    # Double-quoted value
    if ($raw[0] === '"') {
      if (\preg_match('/^"((?:[^"\\\\]|\\\\.)*)"/s', $raw, $m)) {
        return \stripcslashes($m[1]);
      }
      # Unclosed quote — treat remainder as value
      return \substr($raw, 1);
    }

    # Single-quoted value (literal, no escaping)
    if ($raw[0] === "'") {
      if (\preg_match("/^'([^']*)'/", $raw, $m)) {
        return $m[1];
      }
      return \substr($raw, 1);
    }

    # Unquoted — strip inline comments (space/tab followed by #)
    if (\preg_match('/^(.*?)\s+#/', $raw, $m)) {
      return \rtrim($m[1]);
    }

    return \rtrim($raw);
  }
}
