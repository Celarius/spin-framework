<?php declare(strict_types=1);

/**
 * Controller Interface
 *
 * Defines the contract for HTTP controller implementations. Specifies methods
 * for handling different HTTP verbs (GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS)
 * and provides initialization hooks for route-specific middleware.
 *
 * @package  Spin\Core
 * @author   Spin Framework Team
 * @since    1.0.0
 */

namespace Spin\Core;

interface ControllerInterface
{
  /**
   * Initialization method
   *
   * This method is called right after the object has been created before any
   * route specific Middleware handlers
   *
   * @param      array  $args   Path variable arguments as name=value pairs
   */
  function initialize(array $args);

  /**
   * Default handle() method for all HTTP Methods.
   *
   * Calls the appropriate handle*() method.
   *
   * @param      array  $args   Path variable arguments as name=value pairs
   *
   * @return     bool   Value returned by $app->run()
   */
  function handle(array $args);

  /**
   * Handle GET request
   *
   * @param      array  $args   Path variable arguments as name=value pairs
   *
   * @return     bool   Value returned by $app->run()
   */
  public function handleGET(array $args);

  /**
   * Handle POST request
   *
   * @param      array  $args   Path variable arguments as name=value pairs
   *
   * @return     bool   Value returned by $app->run()
   */
  public function handlePOST(array $args);

  /**
   * Handle PUT request
   *
   * @param      array  $args   Path variable arguments as name=value pairs
   *
   * @return     bool   Value returned by $app->run()
   */
  public function handlePUT(array $args);

  /**
   * Handle PATCH request
   *
   * @param      array  $args   Path variable arguments as name=value pairs
   *
   * @return     bool   Value returned by $app->run()
   */
  public function handlePATCH(array $args);

  /**
   * Handle DELETE request
   *
   * @param      array  $args   Path variable arguments as name=value pairs
   *
   * @return     bool   Value returned by $app->run()
   */
  public function handleDELETE(array $args);

  /**
   * Handle HEAD request
   *
   * @param      array  $args   Path variable arguments as name=value pairs
   *
   * @return     bool   Value returned by $app->run()
   */
  public function handleHEAD(array $args);  

  /**
   * Handle OPTIONS request
   *
   * @param      array  $args   Path variable arguments as name=value pairs
   *
   * @return     bool   Value returned by $app->run()
   */
  public function handleOPTIONS(array $args);

  /**
   * Handle custom request
   *
   * @param      array  $args   Path variable arguments as name=value pairs
   *
   * @return     bool   Value returned by $app->run()
   */
  public function handleCUSTOM(array $args);

  /**
   * Return the Client HTTP Request object
   *
   * @return     object
   */
  function getRequest();

  /**
   * Return the Client HTTP Response object
   *
   * @return     object
   */
  function getResponse();

  /**
   * Return the Config object
   *
   * @return     object
   */
  function getConfig();

  /**
   * Return the Logger object
   *
   * @return     object
   */
  function getLogger();

  /**
   * Return the Cache object
   *
   * @return     object
   */
  function getCache();

}
