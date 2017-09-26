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

  /** @var array PSR-11 compatible Container for Dependencies */
  protected $container;

  /** @var Object PSR-16 or PSR-6 compatible Cache */
  protected $cache;

  /** @var Object DB Connections manager */
  protected $connectionManager;



  /**
   * Constructor
   */
  public function __construct()
  {
    parent::__construct();

    try {
      # Require the Global Heloers
      require __DIR__ . '/Helpers.php';

      # Extract Environment
      $this->environment = strtolower(env('ENVIRONMENT','dev'));

      # Set paths
      $this->basePath = realpath(__DIR__ . DIRECTORY_SEPARATOR);
      $this->appPath = realpath(__DIR__);
      $this->storagePath = realpath($this->basePath . DIRECTORY_SEPARATOR . 'storage');

      # Create objects
      $this->config = new Config( $this->appPath, $this->environment );
      $this->logger = new Logger( $this->getAppCode(), $this->config->get('logger') );

      $this->routeGroups = array();
      $this->beforeMiddleware = array();
      $this->afterMiddleware = array();

    } catch (\Exception $e) {
      log()->critical('Failed to create core objectes',['msg'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]);
      die;
    }

    try {
      # Modules
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
      log()->critical('Failed to load module(s)',['msg'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]);
      die;
    }
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
  private function loadFactory(array $params=[])
  {
    if (!empty($params['class']) && class_exists($params['class'])) {
      return new $params['class']($params['options'] ?? array());
    }

    return null;
  }

  /**
   * Run the application
   *
   * @return bool
   */
  public function run(): bool
  {
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
    return $this->config->get('application.name');
  }

  /**
   * Get Application Code - from config-*.json
   *
   * @return string
   */
  public function getAppCode(): string
  {
    return $this->config->get('application.code');
  }

  /**
   * Get Application Version - from config-*.json
   *
   * @return string
   */
  public function getAppVersion(): string
  {
    return $this->config->get('application.version');
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
  public function setResponse(Response $response)
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

}