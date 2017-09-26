<?php declare(strict_types=1);

/**
 * Logger class that extends Monologger
 *
 * Constructor will auto-configure based on configuration options
 */

namespace Spin\Core;

use \Monolog\Handler\StreamHandler;
use \Monolog\Handler\ErrorLogHandler;
use \Monolog\Formatter\LineFormatter;

class Logger extends \Monolog\Logger
{
  /**
   * Logger Constructor
   *
   * @param string $loggerName   Name of the Logger
   * @param array  $options      Array with options from config
   */
  public function __construct(string $loggerName, array $options=[])
  {
    parent::__construct($loggerName);

    # Get the options - Set defaults if not present
    $logLevel = $options['level'] ?? 'error';
    $logDriver = $options['driver'] ?? 'php';
    $logDateFormat = $options['drivers'][$logDriver]['line_datetime'] ?? 'Y-m-d H:i:s';
    $logLineFormat = $options['drivers'][$logDriver]['line_format'] ?? '[%channel%] [%level_name%] %message% %context% %extra%';

    # Create a Line formatter
    $formatter = new LineFormatter($logLineFormat, $logDateFormat);

    # Set options based on FILE or PHP
    if ( strcasecmp($logDriver,"file")==0 ) {
      $logFilePath = $options['drivers'][$logDriver]['file_path'] ?? 'storage/log';
      $logFileFormat = $options['drivers'][$logDriver]['file_format'] ?? 'Y-m-d';

      # Create the Stream Handler
      $handler = new StreamHandler( $logFilePath . DIRECTORY_SEPARATOR . date($logFileFormat) . '.log',
                                    $this->toMonologLevel($logLevel) );

    } else if ( strcasecmp($logDriver,"php")==0 ) {
      # Create the Log Handler
      $handler = new ErrorLogHandler( ErrorLogHandler::OPERATING_SYSTEM,
                                      $this->toMonologLevel($logLevel) );
    } else
      # Fallback handler is PHP own logfile
      $handler = new ErrorLogHandler( ErrorLogHandler::OPERATING_SYSTEM,
                                      $this->toMonologLevel($logLevel) );
    }

    # Set Formatter for $handler
    $handler->setFormatter($formatter);

    # Push handler
    $this->pushHandler($handler);

    # Add a log entry
    $this->debug('Logger created successfully');
  }

}