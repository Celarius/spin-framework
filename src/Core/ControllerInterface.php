<?php declare(strict_types=1);

namespace Spin\Core;

interface ControllerInterface
{
  /**
   * Initialization method
   *
   * This method is called right after the object has been created
   * before any Middleware handlers
   *
   * @param  array $args    Path variable arguments as name=value pairs
   */
  function initialize(array $args);

  /**
   * Handle the Request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   * @return bool           False if failed, True for success
   */
  function handle(array $args);

  /**
   * Return the Client HTTP Request object
   *
   * @return object
   */
  function getRequest();

  /**
   * Return the Client HTTP Response object
   *
   * @return object
   */
  function getResponse();

  /**
   * Return the Config object
   *
   * @return object
   */
  function getConfig();

  /**
   * Return the Logger object
   *
   * @return object
   */
  function getLogger();

  /**
   * Return the Cache object
   *
   * @return object
   */
  function getCache();

}
