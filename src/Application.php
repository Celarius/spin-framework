<?php declare(strict_types=1);

/**
 * Spin Application Class
 *
 * @package   Spin
 */

namespace Spin;

use \GuzzleHttp\Psr7\Request;
use \GuzzleHttp\Psr7\Response;

use \Spin\Core\AbstractBaseClass;
use \Spin\ApplicationInterface;
use \Spin\Core\Config;
use \Spin\Core\Logger;
use \Spin\Core\RouteGroup;
use \Spin\Core\ConnectionManager;
use \Spin\Core\CacheManager;

use \Spin\Core\UploadedFilesManager;
use \Spin\Exceptions\SpinException;
use \Spin\Classes\RequestIdClass;




class Application extends AbstractBaseClass implements ApplicationInterface
{
  /**
   * Application/Framework version
   * @var  string
   */
  const VERSION = '0.0.29';

  /**
   * Application Environment (from ENV vars)
   * @var  string
   */
  protected $environment;

  /**
   * Base path to application folder
   * @var  string
   */
  protected $basePath;

  /**
   * Path to $basePath.'/app' folder
   * @var  string
   */
  protected $appPath;

  /**
   * Path to $basePath.'/storage' folder
   * @var  string
   */
  protected $storagePath;

  /**
   * Path to shared storage
   * @var  string
   */
  protected $sharedStoragePath;

  /**
   * List of Route Groups
   * @var  array<mixed>
   */
  protected $routeGroups;

  /**
   * List of Global Before Middleware
   * @var  array<mixed>
   */
  protected $beforeMiddleware;

  /**
   * List of Global After Middleware
   * @var  array<mixed>
   */
  protected $afterMiddleware;

  /**
   * PHP Error Level we are using
   * @var  int
   */
  protected $errorLevel = \E_ALL;

  /**
   * Config object
   * @var  Config
   */
  protected $config;

  /**
   * Name, Code and Version of App
   * @var  array<mixed>
   */
  protected $version;

  /**
   * PSR-3 compatible Logger object
   * @var  Logger
   */
  protected $logger;

  /**
   * HTTP Factory
   * @var  object
   */
  protected $httpServerRequestFactory;

  /**
   * HTTP Factory
   * @var  object
   */
  protected $httpResponseFactory;

  /**
   * HTTP Factory
   * @var  object
   */
  protected $httpStreamFactory;

  /**
   * Container Factory
   * @var  object
   */
  protected $containerFactory;

  /**
   * List of cookies to send with response
   * @var  array<mixed>
   */
  protected $cookies;

  /**
   * PSR-7 compatible HTTP Server Request
   * @var  Request
   */
  protected $request;

  /**
   * PSR-7 compatible HTTP Response
   * @var  Response
   */
  protected $response;

  /**
   * Name of file to send as response
   * @var  string
   */
  protected $responseFile;

  /**
   * True/False for removing the file after sending to client
   * @var  bool
   */
  protected $responseFileRemove;

  /**
   * PSR-11 compatible Container for Dependencies
   * @var  mixed
   */
  protected $container;

  /**
   * Manager that handles all caches
   * @var  CacheManager
   */
  protected $cacheManager;

  /**
   * DB Connections manager
   * @var  ConnectionManager
   */
  protected $connectionManager;

  /**
   * Uploaded files manager
   * @var  UploadedFilesManager
   */
  protected $uploadedFilesManager;

  /**
   * Error Controllers, key=http code, value=Controller class[@handler]
   * @var  array<mixed>
   */
  protected $errorControllers;

  /**
   * Initial memory usage when SPIN starts
   * @var  int
   */
  protected $initialMemUsage;

  /**
   * Application controlled global vars
   * @var  array<mixed>
   */
  protected $globalVars;


  /**
   * Constructor
   *
   * @param   string  $basePath                                 The base path to the application folder
   */
  public function __construct(string $basePath)
  {
    parent::__construct();

    # Get initial memory usage at beginning
    $this->initialMemUsage = \memory_get_usage();

    # Register the $app variable globally. This allows us to use it immediately
    $GLOBALS['app'] = $this;

    try {
      # Require the Global Helpers
      require __DIR__ . '/Helpers.php';

      # Extract Environment
      $this->setEnvironment( \env('ENVIRONMENT','dev') );

      # Set paths
      $this->basePath = \realpath($basePath);
      $this->appPath = $this->basePath . '/app';
      $this->storagePath = $this->basePath . '/storage';

      # Create config
      $this->config = new Config( $this->appPath, $this->getEnvironment() );

      # Load & Decode the version file
      $verFile = \app()->getConfigPath() . '/version.json';
      $this->version = (\file_exists($verFile) ? \json_decode(\file_get_contents($verFile),true) : []);
      if (empty($this->version['application']['version'] ?? '')) {
        # Backwards compatible with pre "version.json" Spin apps
        $this->version['application']['code'] = \config('application.code');
        $this->version['application']['name'] = \config('application.name');
        $this->version['application']['version'] = \config('application.version');
      }

      # Shared StoragePath
      $this->sharedStoragePath = \config('storage.shared');
      if (!empty($this->sharedStoragePath)) {
        # Append the environment to the path
        $this->sharedStoragePath = $this->sharedStoragePath.'/'.\strtolower($this->getEnvironment()).'/'.\strtolower($this->getAppCode());
      } else {
        # Just use the local storage path instead
        $this->sharedStoragePath = $this->storagePath;
      }

      # Set Timezone - default to UTC
      $timeZone = $this->getConfig()->get('application.global.timezone', 'UTC');
      \date_default_timezone_set($timeZone);

      # Create logger
      $this->logger = new Logger( $this->getAppCode(), $this->getConfig()->get('logger'), $this->basePath );

      # Set error handlers to use Logger component
      $this->setErrorHandlers();

      # Initialize properties
      $this->routeGroups = [];
      $this->beforeMiddleware = [];
      $this->afterMiddleware = [];

      $this->responseFile = '';
      $this->cookies = [];
      // $this->containerFactory = null;
      // $this->request = null;
      // $this->response = null;
      // $this->container = null;

      # Initialize Objects
      $this->httpServerRequestFactory = $this->loadFactory( $this->getConfig()->get('factories.http.serverRequest') ?? ['class'=>'\\Spin\\Factories\\Http\\ServerRequestFactory'] );
      $this->httpResponseFactory = $this->loadFactory( $this->getConfig()->get('factories.http.response') ?? ['class'=>'\\Spin\\Factories\\Http\\ResponseFactory'] );
      $this->httpStreamFactory = $this->loadFactory( $this->getConfig()->get('factories.http.stream') ?? ['class'=>'\\Spin\\Factories\\Http\\StreamFactory'] );

      # Create Cache Manager
      $this->cacheManager = new CacheManager();

      # Create Connection Manager
      $this->connectionManager = new ConnectionManager();

      # HTTP Factories
      $this->request = $this->httpServerRequestFactory->createServerRequestFromArray($serverRequest ?? $_SERVER);
      $this->response = $this->httpResponseFactory->createResponse(404);

      # Container
      $this->containerFactory = $this->loadFactory( ($this->getConfig()->get('factories.container') ?? ['class'=>'\\Spin\\Factories\\ContainerFactory']) );
      $this->container = $this->containerFactory->createContainer();

      # Create & Process UploadedFiles structure
      $this->uploadedFilesManager = new UploadedFilesManager($_FILES);

      # Init internal variables
      $this->responseFile = '';
      $this->responseFileRemove = false;

    } catch (\Exception $e) {
      if ($this->logger) {
        $this->logger->critical('Failed to create core objects',['msg'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]);
      } else {
        \error_log('CRITICAL: '.$e->getMessage().' - '.$e->getTraceAsString());
      }

      # Rethrow the exception
      throw $e;
    }
  }

  /**
   * Run the application
   *
   * @param   array<mixed> $requestVars                       Optional array with server request variables like $_SERVER
   *
   * @return  bool                                            True if application ran successfully
   */
  public function run(array $requestVars=null): bool
  {
    // try {
    //   # Set the Request ID (may be overridden by user specified "RequestIdBeforeMiddleware" if used)
    //   \container('requestId', new RequestIdClass());
    // } catch (\Exception $e) {
    //   $this->getLogger()
    //        ->critical('Failed to create/load module(s)',['msg'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]);
    //   die;
    // }

    # Check and Report on config variables
    $this->checkAndReportConfigVars();

    if ( $this->loadRoutes() ) {
      # Match Route & Run
      $response = $this->runRoute();

      # Set the returned response (Controllers should return Response objects)
      if ( \is_object($response) ) {
        $this->setResponse($response);

        return true;
      }

      # Set the boolean response (old style)
      if (\is_bool($response)){
        return $response;
      }

    }

    return false;
  }

  /**
   * Checks and Reports on config variables
   *
   * @return  void
   */
  protected function checkAndReportConfigVars(): void
  {
    ##
    ## Perform checks on some variables
    ##
    if (!\is_dir($this->sharedStoragePath)) {
      # Attempt to create it
      $ok = \mkdir($this->sharedStoragePath, 0777, true);

      # if it is not found, then report warning
      if (!$ok) {
        $this->getLogger()->warning('config: {shared.storage} path not found',['path'=>$this->sharedStoragePath]);
      }
    }

    return ;
  }

  /**
   * Load the $filename routes file and create all RouteGroups
   *
   * @param   string $filename                                  Filename to load
   *
   * @return  bool                                              True if routes loaded
   *
   * @throws  \Spin\Exceptions\SpinException                    If the routes file is invalid
   */
  protected function loadRoutes(string $filename='')
  {
    # If no filename given, default to "app/Config/routes.json"
    if (empty($filename)) {
      $filename = $this->appPath . \DIRECTORY_SEPARATOR . 'Config' . \DIRECTORY_SEPARATOR . 'routes-' . $this->getEnvironment() . '.json';
    }

    if ( \file_exists($filename) ) {
      $routesFile = \json_decode( \file_get_contents($filename), true );

      if ($routesFile) {

        # Take the GROUPS section
        $routeGroups = $routesFile['groups'] ?? [];

        # Add each RouteGroup
        foreach ($routeGroups as $routeGroupDef)
        {
          # Create new Route Group
          $routeGroup = new RouteGroup($routeGroupDef);

          # Add to list
          $this->routeGroups[] = $routeGroup;
        }

        # Common Middlewares
        $this->beforeMiddleware = ($routesFile['common']['before'] ?? []);
        $this->afterMiddleware = ($routesFile['common']['after'] ?? []);

        # Error controllers
        $this->errorControllers = $routesFile['errors'] ?? [];

      } else {
        throw new SpinException('Invalid routes file "'.$filename.'"');

      }

      # Debug log
      $this->getLogger()->debug('Loaded routes',['file'=>$filename]);

      return true; // routes loaded

    } else {
      # Log
      $this->getLogger()->error('Routes file not found',['file'=>$filename]);

    }

    return false;
  }

  /**
   * Matches & runs route handler matching the Server Request
   *
   * @return  Response|null                                     The matching route group
   */
  protected function runRoute()
  {
    # Get Method and URI
    $httpMethod = $this->getRequest()->getMethod();
    $path = $this->getRequest()->getUri()->getPath();
    $routeInfo = null;
    $response = null;

    # Find route match in groups
    foreach ($this->getRouteGroups() as $routeGroup)
    {
      # Match the METHOD and URI to the routes in this group
      $routeInfo = $routeGroup->matchRoute($httpMethod,$path);

      if ( \count($routeInfo)>0 ) {
        # Debug log
        $this->getLogger()->debug('Route matched ',['path'=>$path,'handler'=>$routeInfo['handler']]);

        # Run Before Hooks
        // $ok = $this->runHooks('OnBeforeRequest');

        $beforeResult = true; // assume all before handlers succeed
        $routeResult = false;
        $afterResult = true; // assume all after handlers succeed


        #
        # Run the Common AND Groups Before Middlewares (ServerRequestInterface)
        #
        $beforeMiddleware = \array_merge($this->beforeMiddleware, $routeGroup->getBeforeMiddleware());

        foreach ($beforeMiddleware as $middleware)
        {
          if (\class_exists($middleware) ) {
            $beforeHandler = new $middleware($routeInfo['args']);

            # Debug log
            $this->getLogger()->debug('Initialize Before middleware',['middleware'=>$middleware]);

            # Initialize
            $beforeHandler->initialize($routeInfo['args']);

            # Debug log
            $this->getLogger()->debug('Running Before middleware',['middleware'=>$middleware]);

            if (!$beforeHandler->handle($routeInfo['args'])) {
              # Record outcome
              $beforeResult = false;

              # Stop processing more middleware
              break;
            }
          } else {
            # Log
            $this->getLogger()->warning('Before Middleware not found',['middleware'=>$middleware]);
          }
        }


        # Make sure we have a requestId at this stage
        if (\is_null(\container('requestId'))) {
          \container('requestId', new RequestIdClass()); // Setting this is a ONE-TIME-OPERATION
        }


        #
        # Create & Run the Controller Class - If the Before Middlewares where ok!
        #
        if ($beforeResult) {
          # Extract class & method
          $arr = \explode('@',$routeInfo['handler']);
          $handlerClass = $arr[0];
          $handlerMethod = ($arr[1] ?? 'handle');

          # Check existance of handler class
          if (\class_exists($handlerClass))
          {
            # Create the class
            $routeHandler = new $handlerClass( $routeInfo['args'] );

            # Check method existance
            if ($routeHandler && \method_exists($routeHandler,'initialize') && \method_exists($routeHandler,$handlerMethod))
            {
              # Debug log
              $this->getLogger()->debug('Running controller->initialize()',[
                'controller' => $handlerClass,
                'rid' => \container('requestId'),
              ]);

              # Initialize
              $routeHandler->initialize($routeInfo['args']);

              # Debug log
              $this->getLogger()->debug('Running controller->handle()',[
                'method' => $handlerMethod,
                'rid' => \container('requestId'),
              ]);

              # Run Controller's method
              $response = $routeHandler->$handlerMethod($routeInfo['args']);

              # Set it
              if ($response)
                $this->setResponse($response);

            } else {
              # Log
              $this->getLogger()->error('Method not found in controller',[
                'controller' => $handlerClass,
                'method' => $handlerMethod,
                'rid' => \container('requestId'),
              ]);
            }

          } else {
            # Debug log
            $this->getLogger()->error('Controller not found',[
              'method' => $this->getRequest()->getMethod(),
              'path' => $path,
              'controller' => $handlerClass,
              'rid' => \container('requestId'),
            ]);

            # Attempt to run the 404 error controller (if set by user in config)
            $this->runErrorController('',404);
          }
        }

        #
        # Run the After Middlewares (ServerRequestInterface)
        #
        $afterMiddleware = \array_merge($this->afterMiddleware,$routeGroup->getAfterMiddleware());
        foreach ($afterMiddleware as $middleware)
        {
          if (\class_exists($middleware) ) {
            $afterHandler = new $middleware($routeInfo['args']);

            # Debug log
            $this->getLogger()->debug('Initialize After middleware',[
              'middleware' => $middleware,
              'rid' => \container('requestId'),
            ]);

            # Initialize
            $afterHandler->initialize($routeInfo['args']);

            # Debug log
            $this->getLogger()->debug('Running After middleware',[
              'middleware' => $middleware,
              'rid' => \container('requestId'),
            ]);

            if (!$afterHandler->handle($routeInfo['args'])) {

              return null;
            }

          } else {
            # Log
            $this->getLogger()->warning('After Middleware not found',[
              'middleware' => $middleware,
              'rid' => \container('requestId'),
            ]);
          }
        }

        # Run After Hooks
        // $ok = $this->runHooks('OnAfterRequest');

        # Return the generated response
        return $this->getResponse();

      } // if count() ...

    } // foreach routeGroup

    ##
    ## No route matched the request ?!
    ##
    $this->getLogger()->info('No route matched the request',[
      'method' => $this->getRequest()->getMethod(),
      'path' => $path,
      'ip' => \getClientIp(),
    ]);

    ##
    ## Try to load the "errors" controllers in the routes-{env}.json file
    ## and run the appropriate HTTP Code controller. If not found
    #W we will try to load the 4xx or 5xx generic controller (if defined)
    ##

    return $this->runErrorController('', 404);
  }

  /**
   * Execute the HandlerMethod of one of the Error Controllers defined in
   * rotues-{env].json}
   *
   * @param   string $body                                      An optional body to send if $httpCode handler not found
   * @param   int|integer $httpCode                             Optional HTTP response code to the run controller
   *
   * @return  Response                                          The response object
   */
  public function runErrorController(string $body, int $httpCode=400)
  {
    $class = '';

    # Determine the Controller Class to run
    if (!\is_null($this->errorControllers) && \array_key_exists($httpCode, $this->errorControllers)) {
      $class = $this->errorControllers[$httpCode];
    } else {
      if ($httpCode>=400 && $httpCode<500) {
        $class = $this->errorControllers['4xx'] ?? '';
      } else
      if ($httpCode>=500) {
        $class = $this->errorControllers['5xx'] ?? '';
      }
    }

    if (!empty($class)) {
      # Extract class & method
      $arr = \explode('@',$class);
      $handlerClass = $arr[0];
      $handlerMethod = ($arr[1] ?? 'handle');

      if (\class_exists($handlerClass)) {
        # Create the class
        $routeHandler = new $class();

        if ($routeHandler) {
          # Initialize
          $routeHandler->initialize([]);

          # Run Controller's handler
          return $routeHandler->$handlerMethod([]);

        } else {
          \logger()->error('Failed to create error controller',[
            'class'=>$handlerClass
          ]);
        }

      } else {
        \logger()->notice('Error controller class does not exist',[
          'class'=>$handlerClass,
          'httpCode'=>$httpCode
        ]);
      }
    }

    # Return a generic empty HTTP response with the $httpCode
    return \response('',$httpCode);
  }

  /**
   * Loads a Factory class
   *
   * @param   string  $params                                   The params found in the config file under the factory
   *
   * @return  object|null                                       Loaded factory object
   *
   * @throws  \Exception                                        If the factory class is not found
   */
  protected function loadFactory(?array $params=[])
  {
    if (\is_array($params) && !empty($params['class']) && \class_exists($params['class'])) {
      $factory = new $params['class']($params['options'] ?? []);
      $this->getLogger()->debug('Factory created',[
        'factory'=>$factory
      ]);

      return $factory;
    } else {
      $this->getLogger()->error('Factory not found',[
        'params'=>$params
      ]);

    }

    return null;
  }

  /**
   * Set the Error Handler
   *
   * @return  bool                                              True if set
   */
  protected function setErrorHandlers()
  {
    # Report all PHP errors (see changelog)
    $this->errorLevel = \error_reporting( \E_ALL | \E_STRICT);

    # set to the user defined error handler
    $old_error_handler = \set_error_handler( array($this,'errorHandler'), E_ALL );
    $old_exception_handler = \set_exception_handler( array($this,'exceptionHandler') );

    \register_shutdown_function( array($this,'fatalErrorhandler') );

    $this->getLogger()->debug('Error handlers set');

    return true;
  }

  /**
   * Error Handler
   *
   * Handles all errors from the code. This is set as the default error handler.
   *
   * @param   string $errNo                                     Error Number
   * @param   string $errStr                                    Error String
   * @param   string $errFile                                   Error File
   * @param   string $errLine                                   Error Line
   * @param   array<mixed> $errContext                          Error Context
   *
   * @return  bool                                              True if handled
   */
  public function errorHandler($errNo, $errStr, $errFile, $errLine, array $errContext=[])
  {
    if (!(\error_reporting() && $errNo)) {
        // This error code is not included in error_reporting, so let it fall
        // through to the standard PHP error handler
        // Example all @ prefixed functions
        return false;
    }

    switch ($errNo) {
      # Emergency

      # Alert

      // # Critical
      // case E_STRICT:
      //   $this->getLogger()->critical("$errStr in file $errFile on line $errLine",$errContext);
      //   exit(1);

      # Error
      case E_ERROR:
      case E_USER_ERROR:
        $this->getLogger()->error("$errStr in file $errFile on line $errLine",$errContext);
          exit(1);

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
   * Handles any Exceptions from the application. This is set as the default
   * exception handler for all exceptions.
   *
   *
   * @param   object $exception                                 The exception object
   *
   * @return  null                                              Null
   */
  public function exceptionHandler($exception)
  {
    if (!\is_null($this->getResponse())) {
      # Run the Error Controller
      $response = $this->runErrorController('',500);

      # Set the error response
      $this->setResponse($response);
    } else {
      # Set HTTP Response Code - we dont even have a response object yet ..
      \http_response_code(500);

    }

    # Log the exception
    $this->getLogger()
         ->critical(
            $exception->getMessage().' in file '.$exception->getFile().' on line '.$exception->getLine(),
            $exception->getTrace()
          );

    return null;
  }

  /**
   * PHP Fatal Error Handler
   *
   * Handles any PHP Fatal Errors.
   *
   * This includes "maximum tmeout", "out of memory", "undefined variable" situations.
   *
   * @return  bool                                             True if handled
   */
  public function fatalErrorhandler()
  {
    # Get last PHP error
    $lastErrorArray = \error_get_last();

    # If no error happened, just exit
    if (!$lastErrorArray) {
      return false;
    }

    # Log the Fatal Error
    $this->getLogger()->critical('PHP Fatal error', array_merge($lastErrorArray,['trace'=>debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)]));

    # Run the Error Controller
    $response = $this->runErrorController($lastErrorArray['message'] ?? 'Unknown',500);

    # Set the error response
    $this->setResponse($response);

    # Set HTTP Response Code
    \http_response_code(500);

    return true;
  }

  /**
   * Set a cookie for the next response
   *
   * Defaults to setting 'samesite'='Strict' to prevent CSRF.
   *
   * @param   string $name                                      The cookie name
   * @param   string|null $value                                The cookie value
   * @param   int|integer $expire                               The cookie expiration time
   * @param   string $path                                      The cookie path
   * @param   string $domain                                    The cookie domain
   * @param   bool|boolean $secure                              The cookie secure flag
   * @param   bool|boolean $httpOnly                            The cookie httpOnly flag
   *
   * @return  mixed
   */
  public function setCookie(string $name, string $value=null, int $expire=0, string $path='', string $domain='', bool $secure=false, bool $httpOnly=false)
  {
    if ( \array_key_exists($name,$this->cookies) && \is_null($value) ) {
      # Remove the Cookie
      $this->cookies = \array_diff_key($this->cookies,[$name=>'']);
    } else {
      # Set the Cookie
      $this->cookies[$name] = [
        'name'     => $name,
        'value'    => $value,
        'expire'   => $expire,
        'path'     => $path,
        'domain'   => $domain,
        'secure'   => $secure,
        'httponly' => $httpOnly,
        'samesite' => 'Strict'      // CSRF protection
      ];
    }

    return true;
  }

  /**
   * getBasePath returns the full path to the application root folder
   *
   * @return  string                                            The base path
   */
  public function getBasePath(): string
  {
    return $this->basePath;
  }

  /**
   * getAppPath returns the full path to the application folder + "/app"
   *
   * @return  string                                            The app path
   */
  public function getAppPath(): string
  {
    return $this->appPath;
  }

  /**
   * getConfigPath returns the full path to the application folder + "/app/Config"
   *
   * @return  string                                            The config path
   */
  public function getConfigPath(): string
  {
    return $this->appPath . \DIRECTORY_SEPARATOR . 'Config';
  }

  /**
   * getStoragePath returns the full path to the application folder + "/storage"
   *
   * @return  string                                            The storage path
   */
  public function getStoragePath(): string
  {
    return $this->storagePath;
  }

  /**
   * getSharedStoragePath returns the full path to the configured shared storage path.
   * If the config does not contain an entry for the shared storage, the result is the same
   * as `getStoragePath()`
   *
   * @return  string                                            The shared storage path
   */
  public function getSharedStoragePath(): string
  {
    if (empty($this->sharedStoragePath)) return $this->getStoragepath();

    return $this->sharedStoragePath;
  }

  /**
   * Returns a $app object property if it exists
   *
   * @param   string $property                                  The property name, or container name to return
   *
   * @return  mixed|null                                        Null if nothing was found
   */
  public function getProperty(string $property)
  {
    if (\property_exists(__CLASS__, $property)) {
      return $this->$property;
    }

    return $this->container($property) ?? null;
  }

  /**
   * Get Application Name - from config-*.json
   *
   * @return  string                                            The application name
   */
  public function getAppName(): string
  {
    return $this->version['application']['name'] ?? '';
  }

  /**
   * Get Application Code - from config-*.json
   *
   * @return  string                                            The application code
   */
  public function getAppCode(): string
  {
    return $this->version['application']['code'] ?? '';
  }

  /**
   * Get Application Version - from config-*.json
   *
   * @return  string                                            The application version
   */
  public function getAppVersion(): string
  {
    return $this->version['application']['version'] ?? '';
  }

  /**
   * Get the HTTP Request (ServerRequest)
   *
   * @return  null|Request                                      The request object
   */
  public function getRequest()
  {
    return $this->request;
  }

  /**
   * Get the HTTP Response (ServerResponse)
   *
   * @return null|Response                                      The response object
   */
  public function getResponse()
  {
    return $this->response;
  }

  /**
   * Set the HTTP Response (ServerResponse)
   *
   * @param   Response $response                                The response object
   *
   * @return  self                                              The current object
   */
  public function setResponse(Response $response)
  {
    $this->response = $response;

    return $this;
  }

  /**
   * Get the Config object
   *
   * @return  object                                            The config object
   */
  public function getConfig()
  {
    return $this->config;
  }

  /**
   * Get the PSR-3 Logger object
   *
   * @return  Logger                                            The logger object
   */
  public function getLogger()
  {
    return $this->logger;
  }

  /**
   * Get the PSR-11 Container object
   *
   * @return  object                                            The container object
   */
  public function getContainer()
  {
    return $this->container;
  }

  /**
   * Get the DB Manager
   *
   * @return  ConnectionManager                                 The connection manager
   */
  public function getConnectionManager(): ?ConnectionManager
  {
    return $this->connectionManager;
  }

  /**
   * Get the Cache Object via CacheManager
   *
   * @param   string  $driverName                               The driver name
   *
   * @return  object                                            The cache object
   */
  public function getCache(string $driverName='')
  {
    return $this->cacheManager->getCache($driverName);
  }

  /**
   * Get the Environment as set in ENV vars
   *
   * @return  string                                            The environment
   */
  public function getEnvironment(): string
  {
    return $this->environment;
  }

  /**
   * Set the Environment where app is running
   *
   * @param   string $environment                               The environment name
   *
   * @return  self                                              The current object
   */
  public function setEnvironment(string $environment)
  {
    $this->environment = \strtolower($environment);

    return $this;
  }

  /**
   * Get a RouteGroup by Name
   *
   * @param   string $groupName                                 The group name
   *
   * @return  null|RouteGroup                                   `null` if not found or The route group
   */
  public function getRouteGroup(string $groupName)
  {
    foreach ($this->routeGroups as $routeGroup)
    {
      if ( \strcasecmp($routeGroup->getName(),$groupName)==0 ) {
        return $routeGroup;
      }
    }

    return null;
  }

  /**
   * Get all RouteGroups
   *
   * @return  array<mixed>                                      The route groups
   */
  public function getRouteGroups(): array
  {
    return $this->routeGroups;
  }

  /**
   * Get or Set a Container value.
   *
   * @param   string $name                                      Dependency name to get or set
   * @param   mixed|null $value                                 Value to SET
   *
   * @return  mixed|null                                        `null` if not found or the value for `$name` in the container
   */
  public function container(string $name, $value=null)
  {
    # Getting or Setting the value?
    if (\is_null($value)) {
      # Return what $name has stored in $container array
      if ($this->getContainer()->has($name)) {
        $value = $this->getContainer()->get($name);
      } else {
        $value = null;
      }

    } elseif (\is_callable($value)) {
      # Callable
      $this->getContainer()->share($name,$value);

    } else {
      # Variable
      $this->getContainer()->share($name,$value);

    }

    return $value;
  }

  /**
   * Set the file to send as response
   *
   * @param   string $filename                                  Filename to send
   * @param   bool $remove                                      Optional. Default `false`. Set to `True` to remove the file after sending
   *
   * @return  self                                              The current object
   */
  public function setFileResponse(string $filename, bool $remove=false)
  {
    $this->responseFile = $filename;
    $this->responseFileRemove = $remove;

    return $this;
  }

  /**
   * Send Response back to client
   *
   * @return  self                                              The current object
   */
  public function sendResponse()
  {
    # Set HTTP Response Code
    \http_response_code($this->getResponse()->getStatusCode());

    # Set All HTTP headers from Response Object
    foreach ($this->getResponse()->getHeaders() as $header => $value) {
      if (\is_array($value)) {
        $values = \implode(';',$value);
      } else {
        $values = $value;
      }

      \header($header.': '.$values);
    }

    # Remove the "x-powered-by" header set by PHP
    if (\function_exists('header_remove')) \header_remove('x-powered-by');

    # Set Cookies
    foreach ($this->cookies as $cookie)
    {
      $cookieOptions = [
        'expires'   => $cookie['expire'] ?? 0,
        'path'      => $cookie['path'] ?? '',
        'domain'    => $cookie['domain'] ?? '',
        'secure'    => $cookie['secure'] ?? false,
        'httponly'  => $cookie['httponly'] ?? true,         // Set HTTPOnly by default (no script access to cookie)
        'samesite'  => $cookie['samesite'] ?? 'Strict',     // Set SameSite=Strict for better CSRF security
      ];

      # Set cookie using $options array (PHP 7.3.0+)
      \setCookie( $cookie['name'],
                  $cookie['value'],
                  $cookieOptions );
    }

    ##
    ## Send a file or a body?
    ##
    if ( !empty($this->responseFile) ) {

      if (\file_exists($this->responseFile)) {
        # Debug log
        $this->getLogger()->debug('Sending file',[
          'code'=>$this->getResponse()->getStatusCode(),
          'headers'=>$this->getResponse()->getHeaders(),
          'file'=>$this->responseFile
        ]);

        # If we have output buffering on, flush it
        if (\ob_get_level()>0 && \ob_get_length()>0) while (@\ob_end_flush());

        # Send the file
        \readfile($this->responseFile);

        # Remove the file if we are told to do so
        if ($this->responseFileRemove) {
          @\unlink($this->responseFile);
        }

        # Reset the values
        $this->responseFile = '';
        $this->responseFileRemove = false;

      } else {
        # Log warning
        $this->getLogger()->warning('File not found',['file'=>$this->responseFile]);

        # Fake a response
        \response('',404);

        # Set HTTP Response Code
        \http_response_code(404);
      }

    } else {
      # Debug log
      $this->getLogger()->debug('Sending body',[
        'code' => $this->getResponse()->getStatusCode(),
        'headers' => $this->getResponse()->getHeaders(),
        'size' => $this->response->getBody()->getSize(),
      ]);

      # Send the Body
      echo (string) $this->response->getBody();
    }

    return $this;
  }

  /**
   * Get the UploadedFilesManager
   *
   * @return  UploadedFilesManager                              The uploaded files manager
   */
  public function getUploadedFilesManager()
  {
    return $this->uploadedFilesManager;
  }

  /**
   * Get the value of initialMemUsage
   *
   * @return  int                                               The initial memory usage
   */
  public function getInitialMemUsage()
  {
    return $this->initialMemUsage;
  }

  /**
   * Get the value of globalVars
   *
   * @return  array<mixed>                                      The global vars
   */
  public function getGlobalVars()
  {
    return $this->globalVars;
  }

  /**
   * Set the value of globalVars
   *
   * @return  self                                              The current object
   */
  public function setGlobalVars($globalVars)
  {
    $this->globalVars = $globalVars;

    return $this;
  }

  /**
   * Get one global var
   *
   * @param   string $id                                        The global variable id
   *
   * @return  null|mixed                                        The global variable
   */
  public function getGlobalVar(string $id)
  {
    return $this->globalVars[$id] ?? null;
  }

  /**
   * Set one global var
   *
   * @param   string $id                                        The global variable id
   * @param   mixed $value                                      The global variable value
   *
   * @return  self                                              The current object
   */
  public function setGlobalVar(string $id, $value)
  {
    $this->globalVars[$id] = $value;

    return $this;
  }
}
