<?php declare(strict_types=1);

/**
 * Spin Framework Config Exception Class
 *
 * Thrown when a configuration error occurs (e.g. invalid or missing JSON
 * configuration file).
 *
 * @package     Spin\Exceptions
 * @author      Spin Framework Team
 * @since       1.0.0
 */

namespace Spin\Exceptions;

use \Spin\Exceptions\SpinException;

class ConfigException extends SpinException
{
}
