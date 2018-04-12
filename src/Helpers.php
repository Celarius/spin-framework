<?php declare(strict_types=1);
/**
 * Creates global functions to make life easier. These functions use
 * the global variable $app to access the application.
 *
 * Also registers global dependencies
 *
 * @package   Spin
 */

/*
  Helpers
  -------
  function env(string $var, $default=null)
  function app(string $property=null)
  function config(string $key=null, string $value=null)
  function container(string $id=null, $value=null)

  function log()
  function db(string $connectionName='')
  function cache(string $driverName='')
  function request()

  function redirect($to = null, $status = 302, $headers = [])
  function response(string $body='', int $code=200, array $headers=[]))
  function responseJson(array $ar=[], int $code=200, int $options=JSON_PRETTY_PRINT|JSON_NUMERIC_CHECK)
  function responseXml(array $ar=[], int $code=200)
  function responseHtml(string $body='', int $code=200, array $headers=[])
 */

use Spin\helpers\ArrayToXml;

if (!function_exists('env')) {
  /**
   * Gets the value of an environment variable. Supports boolean, empty and
   * null.
   *
   * @param      string  $var      Environment Variable to obtain
   * @param      mixed   $default  Default value if not found
   *
   * @return     mixed
   */
  function env(string $var, $default=null)
  {
    # Get from Environmental vars
    $val = getenv($var);

    # If nothing found, return $default
    if ($val === false)
      return $default;

    # Modify "True","False","Null","Empty" values
    switch (strtolower($val)) {
      case 'true':
      case '(true)':
        return true;
      case 'false':
      case '(false)':
        return false;
      case 'empty':
      case '(empty)':
        return '';
      case 'null':
      case '(null)':
        return null;
    }

    # Extract "" encapsulated values
    if ( $val[0]==='"' && $val[-1]==='"' ) {
      return trim($val,'"');
    }

    return $val;
  }
}

if (!function_exists('app')) {
  /**
   * Get the global $app "object" or a $property or a dependancy
   *
   * @param      string  $property  The property
   *
   * @return     mixed
   */
  function app(string $property=null)
  {
    global $app;

    if (is_string($property) && !empty($property) ) {
      return $app->getProperty($property);
    }

    return $app;
  }
}

if (!function_exists('config')) {
  /**
   * Get/Set a Config key/value
   *
   * @param      string          $key    The key in DOt format
   * @param      integer|string  $value  The value
   *
   * @return     mixed
   */
  function config(string $key=null, string $value=null)
  {
    global $app;

    if (is_null($key)) {
      # Return config object
      return $app->getConfig();

    } elseif (is_null($value)) {
      # Return config Key's value
      return $app->getConfig()->get($key);

    } else {
      # Set config $key to $value
      return $app->getConfig()->set($key,$value);

    }
  }
}

if (!function_exists('container')) {
  /**
   * Get or Set an object in the Container
   *
   * When setting a value, it's added using the shared method
   *
   * Examples:
   *
   * // Set a variable to a classname
   * container()->add('MyClass','\\App\\Custom\\MyClass');
   *
   * // Get instance of the Class
   * $myClass = container('MyClass');
   *
   * // Set a key=value in container
   * container('MyKey','1234');
   *
   * // Get the value
   * $value = container('MyKey');
   *
   * @param      string  $id     The identifier
   * @param      mixed   $value  The value
   *
   * @return     mixed
   */
  function container(string $id=null, $value=null)
  {
    global $app;

    if (is_null($id)) {
      # Return the container
      return $app->getContainer();

    } elseif (is_null($value)) {
      # Return the $id in the container
      if ( $app->getContainer()->has($id) ) {
        return $app->getContainer()->get($id);
      } else {
        return null;
      }

    } else {
      # Set $id in container to $value
      return $app->getContainer()->share($id,$value);

    }
  }
}

if (!function_exists('logger')) {
  /**
   * Get the Logger object
   *
   * @return     object
   */
  function logger()
  {
    global $app;

    return $app->getLogger();
  }
}

if (!function_exists('db')) {
  /**
   * Get a Connection object
   *
   * @param      string  $connectionName  The connection name
   * @param      array   $params          The connection parameters
   *
   * @return     object
   */
  function db(string $connectionName='', array $params=[])
  {
    global $app;

    return $app->getConnectionManager()->getConnection($connectionName, $params);
  }
}

if (!function_exists('cache')) {
  /**
   * Get the Cache object
   *
   * @param      string  $driverName  The driver name
   *
   * @return     object
   */
  function cache(string $driverName='')
  {
    global $app;

    return $app->getCache($driverName);
  }
}

if (!function_exists('getRequest')) {
  /**
   * Get the Request object
   *
   * @return     object
   */
  function getRequest()
  {
    global $app;

    return $app->getRequest();
  }
}

if (!function_exists('getResponse')) {
  /**
   * Get the getResponse object
   *
   * @return     object
   */
  function getResponse()
  {
    global $app;

    return $app->getResponse();
  }
}

if (!function_exists('queryParam')) {
  /**
   * Get a Query Param ($_GET variable)
   *
   * @param      string  $paramName
   * @param      mixed   $default
   *
   * @return     mixed
   */
  function queryParam(string $paramName, $default=null)
  {
    global $app;

    return $app->getRequest()->getQueryParams()[$paramName] ?? $default;
  }
}

if (!function_exists('queryParams')) {
  /**
   * Get All Query Params ($_GET variables)
   *
   * @return     array
   */
  function queryParams()
  {
    global $app;

    return $app->getRequest()->getQueryParams() ?? [];
  }
}

if (!function_exists('postParam')) {
  /**
   * Get a Post Param ($_POST variable)
   *
   * @param      string  $paramName
   * @param      mixed   $default
   *
   * @return     mixed
   */
  function postParam(string $paramName, $default=null)
  {
    global $app;

    return $app->getRequest()->getParsedBody()[$paramName] ?? $default;
  }
}

if (!function_exists('cookieParam')) {
  /**
   * Get a Cookie Param ($_COOKIE variable)
   *
   * @param      string  $paramName
   * @param      mixed   $default
   *
   * @return     mixed
   */
  function cookieParam(string $paramName, $default=null)
  {
    global $app;

    return $app->getRequest()->getCookieParams()[$paramName] ?? $default;
  }
}

if (!function_exists('cookieParams')) {
  /**
   * Get all Cookie Params ($_COOKIE variable)
   *
   * @return     array
   */
  function cookieParams()
  {
    global $app;

    return $app->getRequest()->getCookieParams() ?? [];
  }
}

if (!function_exists('cookie')) {
  /**
   * Get/Set Cookies depending on values
   *
   * @param      string   $name      The name
   * @param      ?string  $value     The value
   * @param      integer  $expire    The expire
   * @param      string   $path      The path
   * @param      string   $domain    The domain
   * @param      boolean  $secure    The secure
   * @param      boolean  $httpOnly  The http only
   *
   * @return     mixed
   */
  function cookie(string $name, ?string $value=null, int $expire=0, string $path='', string $domain='', bool $secure=false, bool $httpOnly=false)
  {
    global $app;

    if (is_null($value)) {
      # Read the cookie param
      return cookieParam($name);
    }

    # Set the cookie
    return app()->setCookie($name,$value,$expire,$path,$domain,$secure,$httpOnly);
  }
}


##
## Global Functions for returning responses
##

if (!function_exists('redirect')) {
  /**
   * Redirect the user
   *
   * @param      string  $uri      Where to redirect to. FQDN or relative path
   * @param      int     $status   Status code, defaults to 302
   * @param      array   $headers  Additinal headers
   *
   * @return     object
   */
  function redirect(string $uri, $status=302, $headers = [])
  {
    global $app;

    # Build response object
    $response = getResponse()
                ->withStatus($status)
                ->withHeader('Location',$uri);

    # Set all the headers the user sent
    foreach($headers as $header => $values) {
      $response = $response->withHeader($header,$values);
    }

    # Set it
    $app->setResponse($response);

    return $app->getResponse();
  }
}

if (!function_exists('response')) {
  /**
   * Get/Set the Response to send to the client
   *
   * @param      string                       $body     Body to send
   * @param      int|integer                  $code     HTTP Code
   * @param      array                        $headers  Headers to include
   *
   * @return     \Psr\Http\ResponseInterface
   */
  function response(string $body='', int $code=200, array $headers=[])
  {
    global $app;

    $bStream = app('httpStreamFactory')->createStream($body);

    # Build response object
    $response = getResponse()
                ->withStatus($code)
                ->withBody($bStream);

    # Set all the headers the user sent
    foreach($headers as $header => $values) {
      $response = $response->withHeader($header,$values);
    }

    # Set file to respond with
    $app->setFileResponse('');

    # Set it
    $app->setResponse($response);

    return $app->getResponse();
  }
}

if (!function_exists('responseJson')) {
  /**
   * Send a JSON response with $code and $a content. Also sets the content-type
   * header to "application/json"
   *
   * @param      array     $data     The data
   * @param      integer   $code     The code
   * @param      integer   $options  The options
   * @param      array     $headers  The headers
   *
   * @return     Response
   */
  function responseJson(array $data=[], int $code=200, int $options=JSON_PRETTY_PRINT|JSON_NUMERIC_CHECK, array $headers=[])
  {
    global $app;

    $body = json_encode($data, $options);
    $headers = array_merge(['Content-Type'=>'application/json'],$headers);

    return response($body,$code,$headers);
  }
}

if (!function_exists('responseXml')) {
  /**
   * Build a XML response from the $data supplied
   *
   * @param      array     $data     Array to encode in XML
   * @param      string    $root     Root element
   * @param      int       $code     HTTP Code
   * @param      array     $headers  Headers to include
   *
   * @return     Response
   */
  function responseXml(array $data=[], string $root='xml', int $code=200, array $headers=[])
  {
    global $app;

    $body = xml_encode($a,$options);
    $headers = array_merge(['Content-Type'=>'application/xml'],$headers);

    # Build the XML
    $xmlBuilder = new ArrayToXml();
    $xml = $xmlBuilder->buildXml($data,$root);

    return response($xml,$code,$headers);
  }
}

if (!function_exists('responseHtml')) {
  /**
   * Send a HTML response with $code and $body content.
   *
   * @param      string   $body     The body
   * @param      integer  $code     The code
   * @param      array    $headers  The headers
   *
   * @return     object
   */
  function responseHtml(string $body='', int $code=200, array $headers=[])
  {
    global $app;

    $headers = array_merge(['Content-Type'=>'text/html'],$headers);

    return response($body,$code,$headers);
  }
}

if (!function_exists('responseFile')) {
  /**
   * Send a FILE as a response with $code.
   *
   * @param      string   $filename  The filename
   * @param      integer  $code      The code
   * @param      array    $headers   The headers
   *
   * @return     object
   */
  function responseFile(string $filename, int $code=200, array $headers=[])
  {
    global $app;

    # Set file to respond with
    $app->setFileResponse($filename);

    # Determine Mime-Type for file
    $headers = array_merge(['Content-Type'=>mime_content_type($filename)],$headers);

    return response('',$code,$headers);
  }
}

if (!function_exists('getClientIp')) {
  /**
   * Gets the Clients IPv4 from the request headers
   *
   * @todo       Implement Support for RFC7239
   * @link       https://tools.ietf.org/html/rfc7239
   *
   * @return     string
   */
  function getClientIp(): string
  {
    global $app;

    # Determine Clients IP address
    $ip = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    # Validate it
    $ok = filter_var($ip,FILTER_VALIDATE_IP);

    if ($ok) {
      return $ip;
    }

    return '0.0.0.0'; // Could not determine address/invalid
  }
}

