<?php declare(strict_types=1);

/**
 * AbstractBaseClass
 *
 * All Spin classes are based on this class
 *
 * @package   Spin
 */

namespace Spin\Core;

/**
 * Common foundation for framework classes providing a minimal, consistent
 * inheritance root. Extend this class to align with framework expectations
 * around construction/destruction hooks and future shared concerns.
 */
abstract class AbstractBaseClass
{
  /**
   * Constructor
   */
  public function __construct()
  {
  }

  /**
   * Destructor
   */
  public function __destruct()
  {
  }
}
