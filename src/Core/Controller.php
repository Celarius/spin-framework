<?php declare(strict_types=1);

/**
 * Abstract Controller
 *
 * @package  Spin
 */

namespace Spin\Core;

use \Spin\Core\AbstractBaseClass;
use \Spin\Core\ControllerInterface;

abstract class Controller extends AbstractBaseClass implements ControllerInterface
{
  /**
   * Constructor
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Initialization method
   *
   * This method is called right after the object has been created
   * before any route specific Middleware handlers
   *
   * @param  array $args    Path variable arguments as name=value pairs
   */
  public function initialize(array $args)
  {
    // no code in abstract class
  }

  /**
   * Default handle() method for all HTTP Methods.
   *
   * Calls the appropriate handle*() method.
   *
   * @param  array $args    Path variable arguments as name=value pairs
   * @return bool           Value returned by $app->run()
   */
  public function handle(array $args)
  {
    switch ( strtoupper(request()->getMethod()) ) {
      case "GET"    : return $this->handleGET($args); break;
      case "POST"   : return $this->handlePOST($args); break;
      case "PUT"    : return $this->handlePUT($args); break;
      case "PATCH"  : return $this->handlePATCH($args); break;
      case "DELETE" : return $this->handleDELETE($args); break;
      case "HEAD"   : return $this->handleHEAD($args); break;
      case "OPTIONS": return $this->handleOPTIONS($args); break;
      default       : return $this->handleCUSTOM($args); break;
    }
  }

  /**
   * Handle GET request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   * @return bool           Value returned by $app->run()
   */
  public function handleGET(array $args)
  {
    response('',405);

    return false;
  }

  /**
   * Handle POST request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   * @return bool           Value returned by $app->run()
   */
  public function handlePOST(array $args)
  {
    response('',405);

    return false;
  }

  /**
   * Handle PUT request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   * @return bool           Value returned by $app->run()
   */
  public function handlePUT(array $args)
  {
    response('',405);

    return false;
  }

  /**
   * Handle PATCH request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   * @return bool           Value returned by $app->run()
   */
  public function handlePATCH(array $args)
  {
    response('',405);

    return false;
  }

  /**
   * Handle DELETE request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   * @return bool           Value returned by $app->run()
   */
  public function handleDELETE(array $args)
  {
    response('',405);

    return false;
  }

  /**
   * Handle HEAD request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   * @return bool           Value returned by $app->run()
   */
  public function handleHEAD(array $args)
  {
    response('',405);

    return false;
  }

  /**
   * Handle OPTIONS request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   * @return bool           Value returned by $app->run()
   */
  public function handleOPTIONS(array $args)
  {
    response('',405);

    return false;
  }

  /**
   * Handle custom request
   *
   * @param  array $args    Path variable arguments as name=value pairs
   * @return bool           Value returned by $app->run()
   */
  public function handleCUSTOM(array $args)
  {
    response('',405);

    return false;
  }

  /**
   * Return the Client HTTP Request object
   *
   * @return object
   */
  public function getRequest()
  {
    return request();
  }

  /**
   * Return the Client HTTP Response object
   *
   * @return object
   */
  public function getResponse()
  {
    return response();
  }

  /**
   * Return the Config object
   *
   * @return object
   */
  public function getConfig()
  {
    return config();
  }

  /**
   * Return the Logger object
   *
   * @return object
   */
  public function getLogger()
  {
    return log();
  }

  /**
   * Return the Cache object
   *
   * @return object
   */
  public function getCache()
  {
    return cache();
  }

}
