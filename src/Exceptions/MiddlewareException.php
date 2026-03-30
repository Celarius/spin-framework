<?php declare(strict_types=1);

/**
 * Spin Framework Middleware Exception Class
 *
 * Thrown when middleware processing fails. Reserved for use by middleware
 * implementations in application code.
 *
 * @package     Spin\Exceptions
 * @author      Spin Framework Team
 * @since       1.0.0
 */

namespace Spin\Exceptions;

use \Spin\Exceptions\SpinException;

class MiddlewareException extends SpinException
{
}
