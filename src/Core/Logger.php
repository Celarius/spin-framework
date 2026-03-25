<?php declare(strict_types=1);

/**
 * PSR-3 Compatible Logger Class
 *
 * Extends Monolog to provide a PSR-3 compliant logging implementation.
 * Constructor auto-configures handlers, formatters, and buffering based on
 * configuration options. Supports file and PHP error log drivers with
 * configurable formatting and buffering.
 *
 * @package  Spin
 * @author   Spin Framework Team
 * @since    1.0.0
 */

namespace Spin\Core;

use \Monolog\Handler\StreamHandler;
use \Monolog\Handler\ErrorLogHandler;
use \Monolog\Handler\BufferHandler;
use \Monolog\Formatter\LineFormatter;
use \Monolog\Logger as MonoLogger;

class Logger extends MonoLogger
{
  /**
   * Logger Constructor
   *
   * @param      string  $loggerName  Name of the Logger
   * @param      array   $options     Array with options from config
   * @param      string  $basePath    The base path
   */
  public function __construct(string $loggerName, ?array $options=[], $basePath='')
  {
    parent::__construct($loggerName);

    # Get the options - Set defaults if not present
    $logLevel = $options['level'] ?? 'error';
    $logDriver = $options['driver'] ?? 'php';
    $logDateFormat = $options['drivers'][$logDriver]['line_datetime'] ?? 'Y-m-d H:i:s';
    $logLineFormat = $options['drivers'][$logDriver]['line_format'] ?? '[%channel%] [%level_name%] %message% %context% %extra%';

    # Buffer and Overflow parameters
    $Log_max_buffered_lines = $options['drivers'][$logDriver]['max_buffered_lines'] ?? 0; // Default = 0 - buffer everything
    $Log_flush_overflow_to_disk = $options['drivers'][$logDriver]['flush_overflow_to_disk'] ?? false; // Default = false - Discard overflow (if bufferd lines >0)

    # Create a Line formatter
    $formatter = new LineFormatter($logLineFormat, $logDateFormat);

    # Set options based on FILE or PHP
    if ( \strcasecmp($logDriver,"file")==0 ) {
      $logFilePath = $options['drivers'][$logDriver]['file_path'] ?? 'storage/log';
      $logFileFormat = $options['drivers'][$logDriver]['file_format'] ?? 'Y-m-d';

      # Construct the filename
      $file = $basePath . \DIRECTORY_SEPARATOR . $logFilePath . \DIRECTORY_SEPARATOR . \date($logFileFormat) . '.log';

      # Create the Stream Handler
      $handler = new StreamHandler( $file, $this->toMonologLevel($logLevel) );

    } elseif ( \strcasecmp($logDriver,"php")==0 ) {
      # Create the Log Handler
      $handler = new ErrorLogHandler( ErrorLogHandler::OPERATING_SYSTEM, $this->toMonologLevel($logLevel) );
    } else {
      # Fallback handler is PHP own logfile
      $handler = new ErrorLogHandler( ErrorLogHandler::OPERATING_SYSTEM, $this->toMonologLevel($logLevel) );
    }

    # Set Formatter for $handler
    $handler->setFormatter($formatter);

    # Push Buffer Handler that buffers before the actual user-defined handler
    $this->pushHandler(new BufferHandler($handler, $Log_max_buffered_lines, $this->toMonologLevel($logLevel), true, $Log_flush_overflow_to_disk));

    # Add a log entry
    $this->debug('Logger created successfully');
  }

}
