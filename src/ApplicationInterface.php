<?php declare(strict_types=1);

/**
 * Spin Framework
 *
 * @package   Spin
 */

namespace Spin;

use \GuzzleHttp\Psr7\Request;
use \GuzzleHttp\Psr7\Response;

use \Spin\Core\Config;
use \Spin\Core\Logger;
use \Spin\Core\RouteGroup;
use \Spin\Core\ConnectionManager;
use \Spin\Core\CacheManager;
use \Spin\Core\UploadedFilesManager;
use \Spin\Exceptions\SpinException;


interface ApplicationInterface
{
  /**
   * Run the application
   *
   * @param   ?array $serverRequest                       Optional array with server request variables like $_SERVER
   *
   * @return  bool
   */
  public function run(?array $serverRequest): bool;

  /**
   * Execute the HandlerMethod of one of the Error Controllers defined in
   * routes-{env].json}
   *
   * @param   string $body                                      An optional body to send if $httpCode handler not found
   * @param   int $httpCode                                     Optional HTTP response code to the run controller
   *
   * @return  Response                                          The response object
   */
  public function runErrorController(string $body, int $httpCode = 400): Response;

  /**
   * Error Handler
   *
   * Handles all errors from the code. This is set as the default error handler.
   *
   * @param   int $errNo                                        Error Number
   * @param   string $errStr                                    Error String
   * @param   string $errFile                                   Error File
   * @param   string $errLine                                   Error Line
   * @param   array<mixed> $errContext                          Error Context
   *
   * @return  bool                                              True if handled
   */
  public function errorHandler(int $errNo, $errStr, $errFile, $errLine, array $errContext);

  /**
   * Exception Handler
   *
   * Handles any Exceptions from the application. This is set as the default
   * exception handler for all exceptions.
   *
   *
   * @param   \Exception $exception                             The exception object
   *
   * @return  mixed                                             Null on error or callback to error handler
   */
  public function exceptionHandler(\Exception $exception): mixed;

  /**
   * getBasePath returns the full path to the application root folder
   *
   * @return  string                                            The base path
   */
  public function getBasePath(): string;

  /**
   * getAppPath returns the full path to the application folder + "/app"
   *
   * @return  string                                            The app path
   */
  public function getAppPath(): string;

  /**
   * getConfigPath returns the full path to the application folder + "/app/Config"
   *
   * @return  string                                            The config path
   */
  public function getConfigPath(): string;

  /**
   * getStoragePath returns the full path to the application folder + "/storage"
   *
   * @return  string                                            The storage path
   */
  public function getStoragePath(): string;

  /**
   * getSharedStoragePath returns the full path to the configured shared storage path.
   * If the config does not contain an entry for the shared storage, the result is the same
   * as `getStoragePath()`
   *
   * @return  string                                            The shared storage path
   */
  public function getSharedStoragePath(): string;

  /**
   * Returns a $app object property if it exists
   *
   * @param   string $property                                  The property name, or container name to return
   *
   * @return  mixed                                             Null if nothing was found
   */
  public function getProperty(string $property): mixed;

  /**
   * Get Application Name - from config-*.json
   *
   * @return  string                                            The application name
   */
  public function getAppName(): string;

  /**
   * Get Application Code - from config-*.json
   *
   * @return  string                                            The application code
   */
  public function getAppCode(): string;

  /**
   * Get Application Version - from config-*.json
   *
   * @return  string                                            The application version
   */
  public function getAppVersion(): string;

  /**
   * Get the HTTP Request (ServerRequest)
   *
   * @return  null|Request                                      The request object
   */
  public function getRequest(): ?Request;

  /**
   * Get the HTTP Response (ServerResponse)
   *
   * @return null|Response                                      The response object
   */
  public function getResponse();

  /**
   * Set the HTTP Response (ServerResponse)
   *
   * @param   Response $response                                The response object
   *
   * @return  self                                              The current object
   */
  public function setResponse(Response $response);

  /**
   * Get the Config object
   *
   * @return  object                                            The config object
   */
  public function getConfig();

  /**
   * Get the PSR-3 Logger object
   *
   * @return  Logger                                            The logger object
   */
  public function getLogger();

  /**
   * Get the PSR-11 Container object
   *
   * @return  object                                            The container object
   */
  public function getContainer();

  /**
   * Get the DB Manager
   *
   * @return  ConnectionManager                                 The connection manager
   */
  public function getConnectionManager();

  /**
   * Get the Cache Object via CacheManager
   *
   * @param   string  $driverName                               The driver name
   *
   * @return  object                                            The cache object
   */
  public function getCache(string $driverName='');

  /**
   * Get the Environment as set in ENV vars
   *
   * @return  string                                            The environment
   */
  public function getEnvironment(): string;

  /**
   * Set the Environment where app is running
   *
   * @param   string $environment                               The environment name
   *
   * @return  self                                              The current object
   */
  public function setEnvironment(string $environment);

  /**
   * Get a RouteGroup by Name
   *
   * @param   string $groupName                                 The group name
   *
   * @return  null|RouteGroup                                   `null` if not found or The route group
   */
  public function getRouteGroup(string $groupName);

  /**
   * Get all RouteGroups
   *
   * @return  array<mixed>                                      The route groups
   */
  public function getRouteGroups(): array;

  /**
   * Get or Set a Container value.
   *
   * @param   string $name                                      Dependency name to get or set
   * @param   mixed|null $value                                 Value to SET
   *
   * @return  mixed|null                                        `null` if not found or the value for `$name` in the container
   */
  public function container(string $name, $value=null): mixed;

  /**
   * Set the file to send as response
   *
   * @param   string $filename                                  Filename to send
   * @param   bool $remove                                      Optional. Default `false`. Set to `True` to remove the file after sending
   *
   * @return  self                                              The current object
   */
  public function setFileResponse(string $filename);

  /**
   * Send Response back to client
   *
   * @return  self                                              The current object
   */
  public function sendResponse();

}
