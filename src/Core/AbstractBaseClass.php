<?php declare(strict_types=1);

/**
 * Abstract Base Class
 *
 * Common foundation for all Spin framework classes providing a minimal,
 * consistent inheritance root. Extend this class to align with framework
 * expectations around construction/destruction hooks and future shared concerns.
 *
 * @package   Spin
 * @author    Spin Framework Team
 * @since     1.0.0
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
