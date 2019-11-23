<?php declare(strict_types=1);

/**
 * Spin Framework
 *
 * @package   Spin
 */

namespace Spin;

interface ApplicationInterface
{

  /**
   * Run the application
   *
   * @param      array  $serverRequest  Optional array with server request
   *                                    variables
   *
   * @return     bool
   */
  function run(array $serverRequest=null): bool;

  /**
   * Execute the HandlerMethod of one of the Error Controllers defined in
   * rotues-{env}.json
   *
   * @param      string       $body      An optional body to send if $httpCode
   *                                     handler not found
   * @param      int|integer  $httpCode  HTTP response code to run controller
   *                                     for
   *
   * @return     Response
   */
  function runErrorController(string $body, int $httpCode=400);

  /**
   * Error Handler
   *
   * Handles all errors from the code. This is set as the default error handler.
   *
   * @param      [type]  $errNo       [description]
   * @param      [type]  $errStr      [description]
   * @param      [type]  $errFile     [description]
   * @param      [type]  $errLine     [description]
   * @param      array   $errContext  [description]
   *
   * @return     bool
   */
  function errorHandler($errNo, $errStr, $errFile, $errLine, array $errContext);

  /**
   * Exception Handler
   *
   * Handles any Exceptions from the application. This is set as the default
   * exception handler for all exceptions.
   *
   * @param      [type]  $exception  [description]
   *
   * @return     [type]  [description]
   */
  function exceptionHandler($exception);

  /**
   * getBasePath returns the full path to the application folder
   *
   * @return     string
   */
  function getBasePath(): string;

  /**
   * getAppPath returns the full path to the application folder + "/app"
   *
   * @return     string
   */
  function getAppPath(): string;

  /**
   * getStoragePath returns the full path to the application folder + "/storage"
   *
   * @return     string
   */
  function getStoragePath(): string;

  /**
   * getSharedStoragePath returns the full path to the configured shared storage path.
   * If the config does not contain an entry for the shared storage, the result is the same
   * as `getStoragePath()`
   *
   * @return     string
   */
  function getSharedStoragePath(): string;

  /**
   * Returns a $app object property if it exists
   *
   * @param      string  $property  The property name, or container name to
   *                                return
   *
   * @return     mixed|null  Null if nothing was found
   */
  function getProperty(string $property);

  /**
   * Get Application Name - from config-*.json
   *
   * @return     string
   */
  function getAppName(): string;

  /**
   * Get Application Code - from config-*.json
   *
   * @return     string
   */
  function getAppCode(): string;

  /**
   * Get Application Version - from config-*.json
   *
   * @return     string
   */
  function getAppVersion(): string;

  /**
   * Get the HTTP Request (ServerRequest)
   *
   * @return     object
   */
  function getRequest();

  /**
   * Get the HTTP Response (ServerResponse)
   *
   * @return     object
   */
  function getResponse();

  /**
   * Get the HTTP Response (ServerResponse)
   *
   * @param      \Psr\Http\Respone  $response
   *
   * @return     self
   */
  function setResponse($response);

  /**
   * Get the Config object
   *
   * @return     object
   */
  function getConfig();

  /**
   * Get the PSR-3 Logger object
   *
   * @return     object
   */
  function getLogger();

  /**
   * Get the PSR-11 Container object
   *
   * @return     object
   */
  function getContainer();

  /**
   * Get the DB Manager
   *
   * @return     object
   */
  function getConnectionManager();

  /**
   * Get the Cache Object via CacheManager
   *
   * @param      string  $driverName  The driver name
   *
   * @return     object
   */
  function getCache(string $driverName='');

  /**
   * Get the Environment as set in ENV vars
   *
   * @return     string
   */
  function getEnvironment(): string;

  /**
   * Get a RouteGroup by Name
   *
   * @param      string  $groupName  [description]
   *
   * @return     null  | RouteGroup
   */
  function getRouteGroup(string $groupName);

  /**
   * Get all RouteGroups
   *
   * @return     null  | array
   */
  function getRouteGroups();

  /**
   * Get or Set a Container value.
   *
   * @param      string      $name   Dependency name
   * @param      mixed|null  $value  Value to SET. if Omitted, then $name is
   *                                 returned (if found)
   *
   * @return     mixed|null
   */
  function container(string $name, $value=null);

  /**
   * Set the file to send as response
   *
   * @param      string  $filename  [description]
   *
   * @return     self
   */
  function setFileResponse(string $filename);

  /**
   * Send Response back to client
   *
   * @return     bool
   */
  function sendResponse();

}
