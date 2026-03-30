<?php declare(strict_types=1);

/**
 * Spin Framework Database Exception Class
 *
 * Thrown for database operation errors (e.g. connection failures, query
 * execution errors).
 *
 * @package     Spin\Exceptions
 * @author      Spin Framework Team
 * @since       1.0.0
 */

namespace Spin\Exceptions;

use \Spin\Exceptions\SpinException;

class DatabaseException extends SpinException
{
}
