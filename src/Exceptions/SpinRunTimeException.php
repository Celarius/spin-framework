<?php declare(strict_types=1);

/**
 * Spin Framework Runtime Exception Class
 *
 * Runtime exception class for Spin framework runtime errors. Extends the standard
 * PHP RuntimeException class to provide framework-specific runtime error handling.
 *
 * @package     Spin\Exceptions
 * @author      Spin Framework Team
 * @since       1.0.0
 */

namespace Spin\Exceptions;

Use \RunTimeException;

class SpinRunTimeException extends RunTimeException
{
}
