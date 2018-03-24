<?php declare(strict_types=1);

/**
 * Abstract Controller
 *
 * @package  Spin
 */

namespace Spin\Core;

use Spin\Core\AbstractBaseClass;
use Spin\Core\ControllerInterface;

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
  }

  /**
   * Default handle() method for all HTTP Methods.
   *
   * Calls the appropriate handle*() method.
   *
   * @param      array  $args   Path variable arguments as name=value pairs
   *
   * @return     bool   Value returned by $app->run()
   */
  public function handle(array $args)
  {
    switch ( strtoupper(getRequest()->getMethod()) ) {
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
   * @param      array  $args   Path variable arguments as name=value pairs
   *
   * @return     bool   Value returned by $app->run()
   */
  public function handleGET(array $args)
  {
    $response = $this->verifyGET($args));
    if ($response)
      return $response;

    return response('',405);
  }

  /**
   * Verifies the GET request
   *
   * @param      array          $args   The arguments
   *
   * @return     null|response  If a response is returned, this is sent to the user
   */
  public function verifyGET(array $args)
  {
    return null;
  }


  /**
   * Handle POST request
   *
   * @param      array  $args   Path variable arguments as name=value pairs
   *
   * @return     bool   Value returned by $app->run()
   */
  public function handlePOST(array $args)
  {
    $response = $this->verifyPOST($args));
    if ($response)
      return $response;

    return response('',405);
  }

  /**
   * Verifies the POST request, and the payload
   *
   * @param      array          $args   The arguments
   *
   * @return     null|response  If a response is returned, this is sent to the user
   */
  public function verifyPOST(array $args)
  {
    return null;
  }


  /**
   * Handle PUT request
   *
   * @param      array  $args   Path variable arguments as name=value pairs
   *
   * @return     bool   Value returned by $app->run()
   */
  public function handlePUT(array $args)
  {
    $response = $this->verifyPUT($args));
    if ($response)
      return $response;

    return response('',405);
  }

  /**
   * Verifies the PUT request, and the payload
   *
   * @param      array          $args   The arguments
   *
   * @return     null|response  If a response is returned, this is sent to the user
   */
  public function verifyPUT(array $args)
  {
    return null;
  }


  /**
   * Handle PATCH request
   *
   * @param      array  $args   Path variable arguments as name=value pairs
   *
   * @return     bool   Value returned by $app->run()
   */
  public function handlePATCH(array $args)
  {
    $response = $this->verifyPATCH($args));
    if ($response)
      return $response;

    return response('',405);
  }

  /**
   * Verifies the PATCH request, and the payload
   *
   * @param      array          $args   The arguments
   *
   * @return     null|response  If a response is returned, this is sent to the user
   */
  public function verifyPATCH(array $args)
  {
    return null;
  }


  /**
   * Handle DELETE request
   *
   * @param      array  $args   Path variable arguments as name=value pairs
   *
   * @return     bool   Value returned by $app->run()
   */
  public function handleDELETE(array $args)
  {
    $response = $this->verifyDELETE($args));
    if ($response)
      return $response;

    return response('',405);
  }

  /**
   * Verifies the DELETE request
   *
   * @param      array          $args   The arguments
   *
   * @return     null|response  If a response is returned, this is sent to the user
   */
  public function verifyDELETE(array $args)
  {
    return null;
  }


  /**
   * Handle HEAD request
   *
   * @param      array  $args   Path variable arguments as name=value pairs
   *
   * @return     bool   Value returned by $app->run()
   */
  public function handleHEAD(array $args)
  {
    return response('',405);
  }

  /**
   * Handle OPTIONS request
   *
   * @param      array  $args   Path variable arguments as name=value pairs
   *
   * @return     bool   Value returned by $app->run()
   */
  public function handleOPTIONS(array $args)
  {
    return response('',405);
  }

  /**
   * Handle custom request
   *
   * @param      array  $args   Path variable arguments as name=value pairs
   *
   * @return     bool   Value returned by $app->run()
   */
  public function handleCUSTOM(array $args)
  {
    return response('',405);
  }


  /**
   * Return the Client HTTP Request object
   *
   * @return     object
   */
  public function getRequest()
  {
    return getRequest();
  }

  /**
   * Return the Client HTTP Response object
   *
   * @return     object
   */
  public function getResponse()
  {
    return getResponse();
  }

  /**
   * Return the Config object
   *
   * @return     object
   */
  public function getConfig()
  {
    return config();
  }

  /**
   * Return the Logger object
   *
   * @return     object
   */
  public function getLogger()
  {
    return getLogger();
  }

  /**
   * Return the Cache object
   *
   * @return     object
   */
  public function getCache()
  {
    return cache();
  }

}
