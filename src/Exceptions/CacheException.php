<?php declare(strict_types=1);

/**
 * Spin Framework Cache Exception Class
 *
 * Thrown when a cache adapter encounters an error (e.g. extension not loaded,
 * missing connection options).
 *
 * @package     Spin\Exceptions
 * @author      Spin Framework Team
 * @since       1.0.0
 */

namespace Spin\Exceptions;

use \Spin\Exceptions\SpinException;

class CacheException extends SpinException
{
}
