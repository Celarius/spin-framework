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

if (!function_exists('env')) {
  /**
   * Gets the value of an environment variable. Supports boolean, empty and null.
   *
   * @param  string  $var
   * @param  mixed   $default
   * @return mixed
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
   * @return object
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
   * @return object
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
   *   # Generating new instances of an obj
   *   container()->add('MyClass','\\App\\Custom\\MyClass');
   *   $myClass = container('MyClass'); // Get a new myClass instance
   *
   *   # Retreiving same instance
   *   container()->share('session','\\App\\Custom\\Session');
   *   $session1 = container('session'); // Get the session instance
   *   $session2 = container('session'); // Get the smae session instance
   *
   *   # Quick save/get a value
   *   container('MyKey','1234'); // Store value
   *   $val = container('MyKey'); // Get the value
   *
   * @return mixed
   */
  function container(string $id=null, $value=null)
  {
    global $app;

    if (is_null($id)) {
      # Return the container
      return $app->getContainer();

    } elseif (is_null($value)) {
      # Return the $id i nthe container
      return $app->getContainer()->get($id);

    } else {
      # set $id in container to $value
      return $app->getContainer()->share($id,$value);

    }
  }
}

if (!function_exists('logger')) {
  /**
   * Get the Logger object
   *
   * @return object
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
   * @return object
   */
  function db(string $connectionName='')
  {
    global $app;

    return $app->getConnectionManager()->getConnection($connectionName);
  }
}

if (!function_exists('cache')) {
  /**
   * Get the Cache object
   *
   * @return object
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
   * @return object
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
   * @return object
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
   * @param  string $paramName
   * @param  mixed $default
   * @return mixed
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
   * @return array
   */
  function queryParams()
  {
    return $app->getRequest()->getQueryParams() ?? [];
  }
}

if (!function_exists('postParam')) {
  /**
   * Get a Post Param ($_POST variable)
   *
   * @param  string $paramName
   * @param  mixed $default
   * @return mixed
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
   * @param  string $paramName
   * @param  mixed $default
   * @return mixed
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
   * @return array
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
   * @return mixed
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
   * @param  string  $to        Where to redirect to. FQDN or relative path
   * @param  int     $status    Status code, defaults to 302
   * @param  array   $headers   Additinal headers
   *
   * @return object
   */
  function redirect($to = null, $status = 302, $headers = [])
  {
    # Build response object
    $response = getResponse()
                ->withStatus($status)
                ->withHeader('Location',$to);

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
   * @return \Psr\Http\ResponseInterface
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
   * Send a JSON response with $code and $a content.
   * Also sets the content-type header to "application/json"
   *
   * @param   $data     Array to encode
   * @param   $code     HTTP Code
   * @param   $options  JSON encoding options
   *
   * @return  object
   */
  function responseJson(array $data=[], int $code=200, int $options=JSON_PRETTY_PRINT|JSON_NUMERIC_CHECK)
  {
    global $app;

    $body = json_encode($data, $options);
    $headers = ['Content-Type'=>'application/json'];

    return response($body,$code,$headers);
  }
}

if (!function_exists('responseFile')) {
  /**
   * Send a FILE as a response with $code.
   *
   * @param   $ar       Array to encode
   * @param   $code     HTTP Code
   * @param   $options  JSON encoding options
   *
   * @return  object
   */
  function responseFile(string $filename, int $code=200, array $headers=[])
  {
    global $app;

    # Set file to respond with
    $app->setFileResponse($filename);

    return response('',$code,$headers);
  }
}

if (!function_exists('responseXml')) {
  /**
   * Send a XML response with $code and $a content.
   * Also sets the content-type header to "application/json"
   *
   * @todo    Finish the XML_ENCODE function. WE'd need namespace support etc.
   *
   * @param   $ar       Array to encode
   * @param   $code     HTTP Code
   *
   * @return  object
   */
  function responseXml(array $ar=[], int $code=200)
  {
    global $app;

    $body = xml_encode($a,$options);
    $headers = ['Content-Type'=>'application/xml'];

    return response($body,$code,$headers);
  }
}

if (!function_exists('responseHtml')) {
  /**
   * Send a HTML response with $code and $body content.
   *
   * @param   int    $code     HTTP Code
   * @param   string $html     Array to encode
   *
   * @return  object
   */
  function responseHtml(string $body='', int $code=200, array $headers=[])
  {
    global $app;

    return response($body,$code,$headers);
  }
}
