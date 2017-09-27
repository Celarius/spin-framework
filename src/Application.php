<?php declare(strict_types=1);

/**
 * Spin Framework
 *
 * @package   Spin
 */

namespace Spin;

use \Spin\Core\AbstractBaseClass;
use \Spin\ApplicationInterface;
use \Spin\Core\Config;
use \Spin\Core\Logger;

use Psr\Http\Message\Response;

/**
 * Spin Application
 */
class Application extends AbstractBaseClass implements ApplicationInterface
{
  const VERSION = '0.0.1';

  /** @var string Application Environment (from ENV vars) */
  protected $environment;

  /** @var string Base path to application folder */
  protected $basePath;

  /** @var string Path to $basePath.'/app' folder */
  protected $appPath;

  /** @var string Path to $basePath.'/storage' folder */
  protected $storagePath;

  /** @var array List of Route Groups */
  protected $routeGroups;

  /** @var array List of Global Before Middleware */
  protected $beforeMiddleware;

  /** @var array List of Global After Middleware */
  protected $afterMiddleware;

  /** @var int PHP Error Level we are using */
  protected $errorLevel = E_ALL;


  /** @var Object Config object */
  protected $config;

  /** @var Object PSR-3 compatible Logger object */
  protected $logger;

  /** @var Object HTTP Factory */
  protected $httpFactory;

  /** @var Object Container Factory */
  protected $containerFactory;

  /** @var Object PSR-16 or PSR-6 compatible Cache */
  protected $cacheFactory;



  /** @var Object PSR-7 compatible HTTP Server Request */
  protected $request;

  /** @var Object PSR-7 compatible HTTP Response */
  protected $response;

  /** @var String Name of file to send as response */
  protected $responseFile;

  /** @var array PSR-11 compatible Container for Dependencies */
  protected $container;

  /** @var Object PSR-16 or PSR-6 compatible Cache */
  protected $cache;

  /** @var Object DB Connections manager */
  protected $connectionManager;



  /**
   * Constructor
   */
  public function __construct(string $basePath)
  {
    parent::__construct();

    try {
      # Require the Global Heloers
      require __DIR__ . '/Helpers.php';

      # Extract Environment
      $this->environment = strtolower(env('ENVIRONMENT','dev'));

      # Set paths
      $this->basePath = realpath($basePath);
      $this->appPath = $this->basePath . DIRECTORY_SEPARATOR . 'app';
      $this->storagePath = $this->basePath . DIRECTORY_SEPARATOR . 'storage';

      # Create objects
      $this->config = new Config( $this->appPath, $this->environment );
      $this->logger = new Logger( $this->getAppCode(), $this->config->get('logger') );

      # Set error handlers to use Logger component
      $this->setErrorHandlers();

    } catch (\Exception $e) {
      $this->getLogger()->critical('Failed to create core objectes',['msg'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]);
      die;
    }

    # Initialize the properties
    $this->routeGroups = array();
    $this->beforeMiddleware = array();
    $this->afterMiddleware = array();

    $this->httpFactory = null;;
    $this->request = null;
    $this->response = null;
    $this->responseFile = '';
    $this->containerFactory = null;;
    $this->container = null;;
    $this->cacheFactory = null;;
    $this->cache = null;;
  }

  /**
   * Loads a Factory class
   *
   * @param   string $params     The params found in the config file under the factory
   *
   * @throws  Exception
   *
   * @return  object | null
   */
  private function loadFactory(?array $params=[])
  {
    if (is_array($params) && !empty($params['class']) && class_exists($params['class'])) {
      return new $params['class']($params['options'] ?? array());
    }

    return null;
  }

  /**
   * Set the Error Handler
   *
   * @return bool
   */
  private function setErrorHandlers()
  {
    # Report all PHP errors (see changelog)
    $this->errorLevel = error_reporting( E_ALL | E_STRICT);

    # set to the user defined error handler
    $old_error_handler = set_error_handler(array($this,'errorHandler'), E_ALL);
    $old_exception_handler = set_exception_handler(array($this,'exceptionHandler'));

    return true;
  }


  /**
   * Error Handler
   *
   * Handles all errors from the code. This is set as the default
   * error handler.
   *
   * @param  [type] $errNo       [description]
   * @param  [type] $errStr      [description]
   * @param  [type] $errFile     [description]
   * @param  [type] $errLine     [description]
   * @param  array  $errContext  [description]
   * @return bool
   */
  public function errorHandler($errNo, $errStr, $errFile, $errLine, array $errContext)
  {
    if (!(error_reporting() & $errNo)) {
        // This error code is not included in error_reporting, so let it fall
        // through to the standard PHP error handler
        // Example all @ prefixed functions
        return false;
    }

    switch ($errNo) {
      # Emergency

      # Alert

      # Critical
      case E_STRICT:
        $this->getLogger()->critical("$errStr in file $errFile on line $errLine",$errContext);
        exit(1);
        break;

      # Error
      case E_ERROR:
      case E_USER_ERROR:
        $this->getLogger()->error("$errStr in file $errFile on line $errLine",$errContext);
          exit(1);
          break;

      # Warning
      case E_WARNING:
      case E_USER_WARNING:
        $this->getLogger()->warning("$errStr in file $errFile on line $errLine",$errContext);
        break;

      # Notice
      case E_NOTICE:
      case E_USER_NOTICE:
        $this->getLogger()->notice("$errStr in file $errFile on line $errLine",$errContext);
        break;

      # Info
      case E_RECOVERABLE_ERROR:
      case E_DEPRECATED:
      case E_USER_DEPRECATED:
        $this->getLogger()->info("$errStr in file $errFile on line $errLine",$errContext);
        break;
      default:
        $this->getLogger()->emergency("$errStr in file $errFile on line $errLine",$errContext);
        break;
    }

    # Don't execute PHP internal error handler
    return true;
  }

  /**
   * Exception Handler
   *
   * Handles any Exceptions from the application. This is set as the
   * default exception handler for all exceptions.
   *
   * @param  [type] $exception [description]
   * @return [type]            [description]
   */
  public function exceptionHandler($exception)
  {
    # Set 500 error code as well as something unexpected happened
    // $this->getResponse()->withStatus(500);

    # Log the exception
    $this->getLogger()->critical(
      $exception->getMessage().' in file '.$exception->getFile().' on line '.$exception->getLine(),
      $exception->getTrace()
    );

    # Output a response
    $this->getResponse()->send();
  }


  /**
   * Run the application
   *
   * @return bool
   */
  public function run(): bool
  {
    # Modules
    try {
      $this->httpFactory = null;;
      $this->request = null;
      $this->response = null;
      $this->containerFactory = null;;
      $this->container = null;;
      $this->cacheFactory = null;;
      $this->cache = null;;

      # HTTP Factory
      $this->httpFactory = $this->loadFactory( $this->config->get('factories.http') );
      $this->request = $this->httpFactory->createServerRequestFromArray($_SERVER);
      $this->response = $this->httpFactory->createResponse(404);

      # Container
      $this->containerFactory = $this->loadFactory( $this->config->get('factories.container') );
      $this->container = $this->containerFactory->createContainer();

      # Cache
      $this->cacheFactory = $this->loadFactory( $this->config->get('factories.cache') );
      $this->cache = $this->cacheFactory->createCache();

    } catch (\Exception $e) {
      $this->getLogger()->critical('Failed to load module(s)',['msg'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]);
      die;
    }

    // 1. Run OnBeforeRequest hooks
    //
    // 2. Process Request
    //    a. match route
    //    b. run OnBeforeMiddleware
    //    c. call controller->handle()
    //    d. run OnAfterMiddleware
    //
    // 3. Run OnAfterRequest hooks
  }

  /**
   * getBasePath returns the full path to the application folder
   *
   * @return string
   */
  public function getBasePath() : string
  {
    return $this->basePath;
  }

  /**
   * getAppPath returns the full path to the application folder + "/app"
   *
   * @return string
   */
  public function getAppPath() : string
  {
    return $this->appPath;
  }

  /**
   * getAppPath returns the full path to the application folder + "/storage"
   *
   * @return string
   */
  public function getStoragePath() : string
  {
    return $this->storagePath;
  }

  /**
   * Returns a $app object property if it exists
   *
   * @param  string $property     The property name, or container name to return
   * @return mixed|null           Null if nothing was found
   */
  public function getProperty(string $property)
  {
    if (property_exists(__CLASS__, $property)) {
      return $this->$property;
    }

    return $this->container($property) ?? null;
  }

  /**
   * Get Application Name - from config-*.json
   *
   * @return string
   */
  public function getAppName(): string
  {
    return $this->config->get('application.name','');
  }

  /**
   * Get Application Code - from config-*.json
   *
   * @return string
   */
  public function getAppCode(): string
  {
    return $this->config->get('application.code','');
  }

  /**
   * Get Application Version - from config-*.json
   *
   * @return string
   */
  public function getAppVersion(): string
  {
    return $this->config->get('application.version','');
  }

  /**
   * Get the HTTP Request (ServerRequest)
   *
   * @return object
   */
  public function getRequest()
  {
    return $this->request;
  }

  /**
   * Get the HTTP Response (ServerResponse)
   *
   * @return object
   */
  public function getResponse()
  {
    return $this->response;
  }

  /**
   * Get the HTTP Response (ServerResponse)
   *
   * @param  \Psr\Http\Respone $response
   *
   * @return self
   */
  public function setResponse($response)
  {
    $this->response = $response;

    return $this;
  }

  /**
   * Get the Config object
   *
   * @return object
   */
  public function getConfig()
  {
    return $this->config;
  }

  /**
   * Get the PSR-3 Logger object
   *
   * @return object
   */
  public function getLogger()
  {
    return $this->logger;
  }

  /**
   * Get the DB Manager
   *
   * @return object
   */
  public function getConnectionManager()
  {
    return $this->connectionManager;
  }

  /**
   * Get the Cache Object via CacheManager
   *
   * @return object
   */
  public function getCache(string $driverName='')
  {
    return $this->cacheManager->getCache($driverName);
  }

  /**
   * Get the Environment as set in ENV vars
   *
   * @return string
   */
  public function getEnvironment(): string
  {
    return $this->environment;
  }

  /**
   * Get or Set a Container value.
   *
   * @param  string     $name       Dependency name
   * @param  mixed|null $value      Value to SET. if Omitted, then $name is returned (if found)
   * @return mixed|null
   */
  public function container(string $name, $value=null)
  {
    # Getting or Setting the value?
    if (is_null($value)) {
      # Return what $name has stored in $container array
      $value = $this->container[$name] ?? null;

    } else {
      # Setting the container value $name to $value
      $this->container[$name] = $value;

    }

    return $value;
  }

  /**
   * Set the file to send as response
   *
   * @param   string $filename [description]
   * @return  self
   */
  public function setFileResponse(string $filename)
  {
    $this->responseFile = $filename;

    return $this;
  }

  /**
   * Send Response back to client
   *
   * @return bool
   */
  public function sendResponse()
  {
    # Set HTTP Response Code
    http_response_code($this->response->getStatusCode());

    # Set All HTTP headers from Response Object
    foreach ($this->response->getHeaders() as $header => $value) {
      if (is_array($value)) {
        $values = implode(';',$value);
      } else {
        $values = $value;
      }

      header($header.': '.$values);
    }

    # TODO: Fix cookies
    // # Set all cookies
    // foreach ($this->cookies as $idx => $cookie) {
    //   setCookie( $cookie['name'], $cookie['value'], $cookie['expire'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly'] );
    // }

    # Send Response Body -- if we have something in the $body param
    $body = (string)$this->response->getBody();

    if ( !empty() ) {
    } else
    if ( !empty($this->getFileBody()) ) {
      readfile($this->getFileBody());
    }

    # Results
    return $this;

     return true;
  }
}