<?php declare(strict_types=1);

/**
 * Abstract Controller Base Class
 *
 * Base controller providing default handlers for HTTP verbs and common
 * conveniences for accessing request/response/config/logger/cache. Extend
 * this class and override the relevant handle* methods to implement
 * endpoint-specific behavior.
 *
 * @package  Spin
 * @author   Spin Framework Team
 * @since    1.0.0
 */

namespace Spin\Core;

use \Psr\Http\Message\RequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Spin\Core\AbstractBaseClass;
use \Spin\Core\ControllerInterface;

/**
 * Base controller providing default handlers for HTTP verbs and common
 * conveniences for accessing request/response/config/logger/cache. Extend
 * this class and override the relevant handle* methods to implement
 * endpoint-specific behavior.
 */
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
	 * This method is called right after the object has been created before any
	 * route specific Middleware handlers
	 *
	 * @param      array  $args   Path variable arguments as name=value pairs
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
	 * @return     Response       Value returned by $app->run()
	 */
	public function handle(array $args)
	{
		switch ( \strtoupper(getRequest()->getMethod()) ) {
			case "GET"    : return $this->handleGET($args);
			case "POST"   : return $this->handlePOST($args);
			case "PUT"    : return $this->handlePUT($args);
			case "PATCH"  : return $this->handlePATCH($args);
			case "DELETE" : return $this->handleDELETE($args);
			case "HEAD"   : return $this->handleHEAD($args);
			case "OPTIONS": return $this->handleOPTIONS($args);
			default       : return $this->handleCUSTOM($args);
		}
	}

	/**
	 * Handle GET request
	 *
	 * @param      array  $args   Path variable arguments as name=value pairs
	 *
	 * @return     Response   Value returned by $app->run()
	 */
	public function handleGET(array $args)
	{
		return \response('',405);
	}

	/**
	 * Handle POST request
	 *
	 * @param      array  $args   Path variable arguments as name=value pairs
	 *
	 * @return     Response   Value returned by $app->run()
	 */
	public function handlePOST(array $args)
	{
		return \response('',405);
	}

	/**
	 * Handle PUT request
	 *
	 * @param      array  $args   Path variable arguments as name=value pairs
	 *
	 * @return     Response   Value returned by $app->run()
	 */
	public function handlePUT(array $args)
	{
		return \response('',405);
	}

	/**
	 * Handle PATCH request
	 *
	 * @param      array  $args   Path variable arguments as name=value pairs
	 *
	 * @return     Response   Value returned by $app->run()
	 */
	public function handlePATCH(array $args)
	{
		return \response('',405);
	}

	/**
	 * Handle DELETE request
	 *
	 * @param      array  $args   Path variable arguments as name=value pairs
	 *
	 * @return     Response   Value returned by $app->run()
	 */
	public function handleDELETE(array $args)
	{
		return \response('',405);
	}

	/**
	 * Handle HEAD request
	 *
	 * @param      array  $args   Path variable arguments as name=value pairs
	 *
	 * @return     Response   Value returned by $app->run()
	 */
	public function handleHEAD(array $args)
	{
		return \response('',405);
	}

	/**
	 * Handle OPTIONS request
	 *
	 * @param      array  $args   Path variable arguments as name=value pairs
	 *
	 * @return     Response   Value returned by $app->run()
	 */
	public function handleOPTIONS(array $args)
	{
		return \response('',405);
	}

	/**
	 * Handle custom request
	 *
	 * @param      array  $args   Path variable arguments as name=value pairs
	 *
	 * @return     Response   Value returned by $app->run()
	 */
	public function handleCUSTOM(array $args)
	{
		return \response('',405);
	}

	/**
	 * Return the Client HTTP Request object
	 *
	 * @return     object
	 */
	public function getRequest()
	{
		return \getRequest();
	}

	/**
	 * Return the Client HTTP Response object
	 *
	 * @return     object
	 */
	public function getResponse()
	{
		return \getResponse();
	}

	/**
	 * Return the Config object
	 *
	 * @return     object
	 */
	public function getConfig()
	{
		return \config();
	}

	/**
	 * Return the Logger object
	 *
	 * @return     object
	 */
	public function getLogger()
	{
		return \logger();
	}

	/**
	 * Return the Cache object
	 *
	 * @return     object
	 */
	public function getCache()
	{
		return \cache();
	}
}
