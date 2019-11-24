<?php declare(strict_types=1);

/**
 * Spin Application Class
 *
 * @package   Spin
 */

namespace Spin;

use \Spin\Core\AbstractBaseClass;
use \Spin\ApplicationInterface;
use \Spin\Core\Config;
use \Spin\Core\Logger;
use \Spin\Core\RouteGroup;
use \Spin\Core\ConnectionManager;
use \Spin\Core\CacheManager;
use \Spin\Core\UploadedFilesManager;
use \Spin\Exceptions\SpinException;
use \Psr\Http\Message\Response;

class Application extends AbstractBaseClass implements ApplicationInterface
{
  /** @const      string          Application/Framework version */
  const VERSION = '0.0.9';

  /** @var        string          Application Environment (from ENV vars) */
  protected $environment;

  /** @var        string          Base path to application folder */
  protected $basePath;

  /** @var        string          Path to $basePath.'/app' folder */
  protected $appPath;

  /** @var        string          Path to $basePath.'/storage' folder */
  protected $storagePath;

  /** @var        string          Path to shared storage */
  protected $sharedStoragePath;

  /** @var        array           List of Route Groups */
  protected $routeGroups;

  /** @var        array           List of Global Before Middleware */
  protected $beforeMiddleware;

  /** @var        array           List of Global After Middleware */
  protected $afterMiddleware;

  /** @var        int             PHP Error Level we are using */
  protected $errorLevel = \E_ALL;

  /** @var        Object          Config object */
  protected $config;

  /** @var        Array           Name, Code and Version of App */
  protected $version;

  /** @var        Object          PSR-3 compatible Logger object */
  protected $logger;

  /** @var        Object          HTTP Factory */
  protected $httpServerRequestFactory;

  /** @var        Object          HTTP Factory */
  protected $httpResponseFactory;

  /** @var        Object          HTTP Factory */
  protected $httpStreamFactory;

  /** @var        Object          Container Factory */
  protected $containerFactory;

  /** @var        array           List of cookies to send with response */
  protected $cookies;

  /** @var        Object          PSR-7 compatible HTTP Server Request */
  protected $request;

  /** @var        Object          PSR-7 compatible HTTP Response */
  protected $response;

  /** @var        String          Name of file to send as response */
  protected $responseFile;

  /** @var        array           PSR-11 compatible Container for Dependencies */
  protected $container;

  /** @var        Object          Manager that handles all caches */
  protected $cacheManager;

  /** @var        Object          DB Connections manager */
  protected $connectionManager;

  /** @var        Object          Uploaded files manager */
  protected $uploadedFilesManager;

  /** @var        array           Error Controllers, key=http code, value=Controller class[@handler] */
  protected $errorControllers;

  /**
   * Constructor
   *
   * @param      string  $basePath  The base path to the application folder
   */
  public function __construct(string $basePath)
  {
    parent::__construct();

    # Register the $app variable globally. This allows us to use it immediately
    $GLOBALS['app'] = $this;

    try {
      # Require the Global Heloers
      require __DIR__ . '/Helpers.php';

      # Extract Environment
      $this->setEnvironment( \env('ENVIRONMENT','dev') );

      # Set paths
      $this->basePath = \realpath($basePath);
      $this->appPath = $this->basePath . \DIRECTORY_SEPARATOR . 'app';
      $this->storagePath = $this->basePath . \DIRECTORY_SEPARATOR . 'storage';

      # Create config
      $this->config = new Config( $this->appPath, $this->getEnvironment() );

      # Shared StoragePath
      $this->sharedStoragePath = \config('storage.shared');
      if (!empty($this->sharedStoragePath)) {
        # Append the environment to the path
        $this->sharedStoragePath .= \DIRECTORY_SEPARATOR . \strtolower($this->getEnvironment()) . \strtolower($this->getAppCode());
      } else {
        # Just use the local storage path instead
        $this->sharedStoragePath = $this->storagePath;
      }

      # Load & Decode the version file
      $verFile = \app()->getConfigPath() . \DIRECTORY_SEPARATOR . 'version.json';
      $this->version = (\file_exists($verFile) ? \json_decode(\file_get_contents($verFile),true) : []);
      if (empty($this->version['application']['version'] ?? '')) {
        # Backwards compatible with pre "version.json" Spin apps
        $this->version['application']['code'] = \config('application.code');
        $this->version['application']['name'] = \config('application.name');
        $this->version['application']['version'] = \config('application.version');
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

    } catch (\Exception $e) {
      if ($this->logger) {
        $this->logger->critical('Failed to create core objectes',['msg'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]);
      } else {
        \error_log('CRITICAL: '.$e->getMessage().' - '.$e->getTraceAsString());
      }
      die;
    }
  }

  /**
   * Run the application
   *
   * @param      array  $serverRequest  Optional array with server request
   *                                    variables like $_SERVER
   *
   * @return     bool
   */
  public function run(array $serverRequest=null): bool
  {
    try {
      # Set the Request ID (may be overridden by user specified "RequestIdBeforeMiddleware" if used)
      \container('requestId', \md5((string)\microtime(true)));

    } catch (\Exception $e) {
      $this->getLogger()
           ->critical('Failed to create/load module(s)',['msg'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]);

      die;
    }

    # Check and Report on config variables
    $this->checkAndReportConfigVars();

    # Load Routes
    $ok = $this->loadRoutes();

    if ( $ok ) {
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
   * @return void
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
   * @param      string                          $filename  [description]
   *
   * @throws     \Spin\Exceptions\SpinException
   *
   * @return     bool
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
   * @return     array  The matching route group
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
            $this->getLogger()->debug('Initialize Before middleware',['rid'=>container('requestId'),'middleware'=>$middleware]);

            # Initialize
            $beforeHandler->initialize($routeInfo['args']);

            # Debug log
            $this->getLogger()->debug('Running Before middleware',['rid'=>container('requestId'),'middleware'=>$middleware]);

            if (!$beforeHandler->handle($routeInfo['args'])) {
              # Record outcome
              $beforeResult = false;

              # Stop processing more middleware
              break;
            }
          } else {
            # Log
            $this->getLogger()->warning('Before Middleware not found',['rid'=>container('requestId'),'middleware'=>$middleware]);
          }
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
                'rid' => \container('requestId'),
                'controller'=>$handlerClass
              ]);

              # Initialize
              $routeHandler->initialize($routeInfo['args']);

              # Debug log
              $this->getLogger()->debug('Running controller->handle()',[
                'rid' => \container('requestId'),
                'method'=>$handlerMethod
              ]);

              # Run Controller's method
              $response = $routeHandler->$handlerMethod($routeInfo['args']);

              # Set it
              if ($response)
                $this->setResponse($response);

            } else {
              # Log
              $this->getLogger()->error('Method not found in controller ',[
                'rid' => \container('requestId'),
                'controller'=>$handlerClass,
                'method'=>$handlerMethod
              ]);
            }

          } else {
            # Debug log
            $this->getLogger()->error('Controller not found ',[
              'rid' => \container('requestId'),
              'controller'=>$handlerClass
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
              'rid' => \container('requestId'),
              'middleware'=>$middleware
            ]);

            # Initialize
            $afterHandler->initialize($routeInfo['args']);

            # Debug log
            $this->getLogger()->debug('Running After middleware',[
              'rid' => \container('requestId'),
              'middleware'=>$middleware
            ]);

            if (!$afterHandler->handle($routeInfo['args'])) {

              return false;
            }

          } else {
            # Log
            $this->getLogger()->warning('After Middleware not found',[
              'rid' => \container('requestId'),
              'middleware'=>$middleware
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
    $this->getLogger()->notice('No route matched the request',[
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
   * @param      string       $body      An optional body to send if $httpCode
   *                                     handler not found
   * @param      int|integer  $httpCode  HTTP response code to run controller
   *                                     for
   *
   * @return     Response
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
      if ($httpCode>=400 && $httpCode<500) {
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
          logger()->error('Failed to create error controller',[
            'class'=>$handlerClass
          ]);
        }

      } else {
        logger()->notice('Error controller class does not exist',[
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
   * @param      string  $params  The params found in the config file under the
   *                              factory
   *
   * @throws     Exception
   *
   * @return     object  | null
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
   * @return     bool
   */
  protected function setErrorHandlers()
  {
    # Report all PHP errors (see changelog)
    $this->errorLevel = \error_reporting( E_ALL | E_STRICT);

    # set to the user defined error handler
    $old_error_handler = \set_error_handler(array($this,'errorHandler'), E_ALL);
    $old_exception_handler = \set_exception_handler(array($this,'exceptionHandler'));

    $this->getLogger()->debug('Error handlers set');

    return true;
  }

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
  public function errorHandler($errNo, $errStr, $errFile, $errLine, array $errContext)
  {
    if (!(\error_reporting() & $errNo)) {
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
   * Handles any Exceptions from the application. This is set as the default
   * exception handler for all exceptions.
   *
   * @param      Object  $exception  [description]
   *
   * @return     null
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
   * Set a cookie for the next response
   *
   * @param      string        $name      [description]
   * @param      string|null   $value     [description]
   * @param      int|integer   $expire    [description]
   * @param      string        $path      [description]
   * @param      string        $domain    [description]
   * @param      bool|boolean  $secure    [description]
   * @param      bool|boolean  $httpOnly  [description]
   *
   * @return     mixed
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
        'httpOnly' => $httpOnly
      ];
    }

    return true;
  }

  /**
   * getBasePath returns the full path to the application root folder
   *
   * @return     string
   */
  public function getBasePath(): string
  {
    return $this->basePath;
  }

  /**
   * getAppPath returns the full path to the application folder + "/app"
   *
   * @return     string
   */
  public function getAppPath(): string
  {
    return $this->appPath;
  }

  /**
   * getConfigPath returns the full path to the application folder + "/app/Config"
   *
   * @return     string
   */
  public function getConfigPath(): string
  {
    return $this->appPath . \DIRECTORY_SEPARATOR . 'Config';
  }

  /**
   * getStoragePath returns the full path to the application folder + "/storage"
   *
   * @return     string
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
   * @return     string
   */
  public function getSharedStoragePath(): string
  {
    if (empty($this->sharedStoragePath)) return $this->getStoragepath();

    return $this->sharedStoragePath;
  }

  /**
   * Returns a $app object property if it exists
   *
   * @param      string      $property  The property name, or container name to
   *                                    return
   *
   * @return     mixed|null  Null if nothing was found
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
   * @return     string
   */
  public function getAppName(): string
  {
    return $this->version['application']['name'] ?? '';
  }

  /**
   * Get Application Code - from config-*.json
   *
   * @return     string
   */
  public function getAppCode(): string
  {
    return $this->version['application']['code'] ?? '';
  }

  /**
   * Get Application Version - from config-*.json
   *
   * @return     string
   */
  public function getAppVersion(): string
  {
    return $this->version['application']['version'] ?? '';
  }

  /**
   * Get the HTTP Request (ServerRequest)
   *
   * @return     object
   */
  public function getRequest()
  {
    return $this->request;
  }

  /**
   * Get the HTTP Response (ServerResponse)
   *
   * @return     object
   */
  public function getResponse()
  {
    return $this->response;
  }

  /**
   * Get the HTTP Response (ServerResponse)
   *
   * @param      \Psr\Http\Respone  $response
   *
   * @return     self
   */
  public function setResponse($response)
  {
    $this->response = $response;

    return $this;
  }

  /**
   * Get the Config object
   *
   * @return     object
   */
  public function getConfig()
  {
    return $this->config;
  }

  /**
   * Get the PSR-3 Logger object
   *
   * @return     object
   */
  public function getLogger()
  {
    return $this->logger;
  }

  /**
   * Get the PSR-11 Container object
   *
   * @return     object
   */
  public function getContainer()
  {
    return $this->container;
  }

  /**
   * Get the DB Manager
   *
   * @return     object
   */
  public function getConnectionManager()
  {
    return $this->connectionManager;
  }

  /**
   * Get the Cache Object via CacheManager
   *
   * @param      string  $driverName  The driver name
   *
   * @return     object
   */
  public function getCache(string $driverName='')
  {
    return $this->cacheManager->getCache($driverName);
  }

  /**
   * Get the Environment as set in ENV vars
   *
   * @return     string
   */
  public function getEnvironment(): string
  {
    return $this->environment;
  }

  /**
   * Set the Environment
   *
   * @param      string  $environment  The environment
   *
   * @return     self
   */
  public function setEnvironment(string $environment)
  {
    $this->environment = strtolower($environment);

    return $this;
  }

  /**
   * Get a RouteGroup by Name
   *
   * @param      string  $groupName  [description]
   *
   * @return     null    | RouteGroup
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
   * @return     null  | array
   */
  public function getRouteGroups()
  {
    return $this->routeGroups;
  }

  /**
   * Get or Set a Container value.
   *
   * @param      string      $name   Dependency name
   * @param      mixed|null  $value  Value to SET. if Omitted, then $name is
   *                                 returned (if found)
   *
   * @return     mixed|null
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
   * @param      string  $filename  [description]
   *
   * @return     self
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
      \setCookie( $cookie['name'],
                  $cookie['value'],
                  $cookie['expire'] ?? 0,
                  $cookie['path'] ?? '',
                  $cookie['domain'] ?? '',
                  $cookie['secure'] ?? false,
                  $cookie['httponly'] ?? false
                );
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

        # Send the file
        \readfile($this->responseFile);

      } else {
        # Log warning
        $this->getLogger()->warning('File not found',['file'=>$this->responseFile]);

        # Fake a response
        \response('',404);

        # Set HTTP Response Code
        \http_response_code(404);
      }

    } else {
      # Get body
      $body = (string)$this->response->getBody();

      # Debug log
      $this->getLogger()->debug('Sending body',[
        'code' => $this->getResponse()->getStatusCode(),
        'headers' => $this->getResponse()->getHeaders(),
        'size' => \strlen($body)
      ]);

      # Send the Body
      echo $body;
    }

    return $this;
  }

  /**
   * Get the UploadedFilesManager
   *
   * @return     UploadedFilesManager
   */
  public function getUploadedFilesManager()
  {
    return $this->uploadedFilesManager;
  }

}