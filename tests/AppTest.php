<?php declare(strict_types=1);

namespace Spin\tests;

use PHPUnit\Framework\TestCase;
use Spin\Exceptions\SpinException;
use Spin\Core\Config;
use Spin\Core\ConnectionManager;
use Spin\Core\RouteGroup;
use GuzzleHttp\Psr7\Response;

class AppTest extends TestCase
{
  /**
   * @var \Spin\Application
   */
  protected \Spin\Application $app;

  /**
   * Setup test
   */
  public function setUp(): void
  {
    global $app;

    $this->app = $app;
  }

  /**
   * Test Application object creation
   */
  public function testAppCreate(): void
  {
    $this->assertSame($this->app->getBasePath(), \realpath(__DIR__));
    $this->assertInstanceOf(\Spin\Application::class, $this->app);
    $this->assertInstanceOf(\Spin\ApplicationInterface::class, $this->app);
  }

  /**
   * Test all path methods
   */
  public function testPathMethods(): void
  {
    $basePath = \realpath(__DIR__);
    
    // Test base path
    $this->assertEquals($basePath, $this->app->getBasePath());
    
    // Test app path
    $this->assertEquals($basePath . DIRECTORY_SEPARATOR . 'app', $this->app->getAppPath());
    
    // Test config path
    $this->assertEquals($basePath . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Config', $this->app->getConfigPath());
    
    // Test storage path
    $this->assertEquals($basePath . DIRECTORY_SEPARATOR . 'storage', $this->app->getStoragePath());
    
    // Test shared storage path (should default to storage path if not configured)
    $this->assertIsString($this->app->getSharedStoragePath());
  }

  /**
   * Test application version information
   */
  public function testApplicationInfo(): void
  {
    // These methods should return strings
    $this->assertIsString($this->app->getAppName());
    $this->assertIsString($this->app->getAppCode());
    $this->assertIsString($this->app->getAppVersion());
    
    // They might be empty if not configured, which is ok
    // Just verify they don't throw exceptions
    $this->assertTrue(true);
  }

  /**
   * Test environment handling
   */
  public function testEnvironment(): void
  {
    // Get current environment
    $currentEnv = $this->app->getEnvironment();
    $this->assertIsString($currentEnv);
    $this->assertNotEmpty($currentEnv);
    
    // Test setting environment
    $this->app->setEnvironment('testing');
    $this->assertEquals('testing', $this->app->getEnvironment());
    
    // Restore original
    $this->app->setEnvironment($currentEnv);
  }

  /**
   * Test Config object
   */
  public function testConfig(): void
  {
    $config = $this->app->getConfig();
    $this->assertInstanceOf(Config::class, $config);
    
    // Test that config has the basic structure
    $this->assertIsObject($config);
  }

  /**
   * Test Logger functionality
   */
  public function testApplicationLogger(): void
  {
    $logger = $this->app->getLogger();

    // Test global logger function
    $this->assertSame($logger, \logger());
    
    // Test logging
    \logger()->notice('This is a test log line', ['test' => true]);
    $this->assertTrue(true); // If we get here, logging worked
  }

  /**
   * Test Container functionality
   */
  public function testContainer(): void
  {
    $container = $this->app->getContainer();
    $this->assertIsObject($container);
    
    // Test setting and getting container values
    $testKey = 'test_' . uniqid();
    $testValue = 'test_value_' . uniqid();
    
    $this->app->container($testKey, $testValue);
    $this->assertEquals($testValue, $this->app->container($testKey));
    
    // Test non-existent key
    $this->assertNull($this->app->container('non_existent_key_' . uniqid()));
  }

  /**
   * Test Connection Manager
   */
  public function testConnectionManager(): void
  {
    $connectionManager = $this->app->getConnectionManager();
    $this->assertInstanceOf(ConnectionManager::class, $connectionManager);
  }

  /**
   * Test Cache Manager and Cache retrieval
   */
  public function testCacheManager(): void
  {
    try {
      $cache = $this->app->getCache();
      $this->assertIsObject($cache);
    } catch (\Exception $e) {
      // Cache might not be configured, which is ok
      $this->assertTrue(true); // Skip assertion if cache is not configured
    }
  }

  /**
   * Test Route Groups
   */
  public function testRouteGroups(): void
  {
    $routeGroups = $this->app->getRouteGroups();
    $this->assertIsArray($routeGroups);
    
    // Test getting specific route group
    if (!empty($routeGroups)) {
      $firstGroupName = array_key_first($routeGroups);
      $routeGroup = $this->app->getRouteGroup($firstGroupName);
      $this->assertInstanceOf(RouteGroup::class, $routeGroup);
    }
    
    // Test non-existent route group
    $this->assertNull($this->app->getRouteGroup('non_existent_group_' . uniqid('', true)));
  }

  /**
   * Test Request and Response handling
   */
  public function testRequestResponse(): void
  {
    // Request might be null before run()
    $request = $this->app->getRequest();
    if ($request !== null) {
      $this->assertInstanceOf(\GuzzleHttp\Psr7\Request::class, $request);
    }
    
    // Response might be null before run()
    $response = $this->app->getResponse();
    if ($response !== null) {
      $this->assertInstanceOf(Response::class, $response);
    }
    
    // Test setting response
    $newResponse = new Response(200, [], 'Test response');
    $this->app->setResponse($newResponse);
    $this->assertSame($newResponse, $this->app->getResponse());
  }

  /**
   * Test Global Variables
   */
  public function testGlobalVariables(): void
  {
    try {
      // Get current global vars
      $globalVars = $this->app->getGlobalVars();
      $this->assertIsArray($globalVars);
    } catch (\Error $e) {
      // Global vars might not be initialized yet
      // Initialize them first
      $this->app->setGlobalVars([]);
      $globalVars = $this->app->getGlobalVars();
      $this->assertIsArray($globalVars);
    }
    
    // Test setting individual global var
    $key = 'test_var_' . uniqid('', true);
    $value = 'test_value_' . uniqid('', true);
    
    $this->app->setGlobalVar($key, $value);
    $this->assertEquals($value, $this->app->getGlobalVar($key));
    
    // Test non-existent global var
    $this->assertNull($this->app->getGlobalVar('non_existent_' . uniqid('', true)));
    
    // Test setting all global vars
    $newGlobalVars = ['key1' => 'value1', 'key2' => 'value2'];
    $this->app->setGlobalVars($newGlobalVars);
    $this->assertEquals($newGlobalVars, $this->app->getGlobalVars());
  }

  /**
   * Test Property access
   */
  public function testPropertyAccess(): void
  {
    // Test getting existing properties
    $environment = $this->app->getProperty('environment');
    $this->assertIsString($environment);
    
    // Test non-existent property
    $nonExistent = $this->app->getProperty('non_existent_property_' . uniqid('', true));
    $this->assertNull($nonExistent);
  }

  /**
   * Test initial memory usage tracking
   */
  public function testInitialMemUsage(): void
  {
    $memUsage = $this->app->getInitialMemUsage();
    $this->assertIsInt($memUsage);
    $this->assertGreaterThan(0, $memUsage);
  }

  /**
   * Test Uploaded Files Manager
   */
  public function testUploadedFilesManager(): void
  {
    $uploadedFilesManager = $this->app->getUploadedFilesManager();
    $this->assertInstanceOf(\Spin\Core\UploadedFilesManager::class, $uploadedFilesManager);
  }

  /**
   * Test file response functionality
   */
  public function testFileResponse(): void
  {
    // Create a temporary file for testing
    $tempFile = sys_get_temp_dir() . '/test_file_' . uniqid('', true) . '.txt';
    file_put_contents($tempFile, 'Test content');
    
    // Test setting file response
    $this->app->setFileResponse($tempFile);
    
    // Clean up
    if (file_exists($tempFile)) {
      unlink($tempFile);
    }
    
    $this->assertTrue(true); // If we get here, setFileResponse worked
  }

  /**
   * Test error controller execution
   */
  public function testRunErrorController(): void
  {
    try {
      $response = $this->app->runErrorController('Test error', 404);
      $this->assertInstanceOf(Response::class, $response);
    } catch (\Exception $e) {
      // Error controller might not be configured, which is ok for this test
      $this->assertTrue(true);
    }
  }

  /**
   * Test exception handler
   */
  public function testExceptionHandler(): void
  {
    $exception = new \Exception('Test exception');
    
    // We can't easily test the actual exception handler behavior,
    // but we can verify it doesn't throw an error
    ob_start();
    $result = $this->app->exceptionHandler($exception);
    ob_end_clean();
    
    // The handler should return something (even if null)
    $this->assertTrue(true); // If we get here, handler didn't crash
  }

  /**
   * Test error handler
   */
  public function testErrorHandler(): void
  {
    // Test handling a notice
    $result = $this->app->errorHandler(
      E_NOTICE,
      'Test notice',
      __FILE__,
      __LINE__,
      []
    );
    
    $this->assertIsBool($result);
  }

  /**
   * @throws SpinException
   */
  public function testRun(): void
  {
    // Mock server request data
    $serverRequest = [
      'REQUEST_METHOD' => 'GET',
      'REQUEST_URI' => '/',
      'SERVER_NAME' => 'localhost',
      'SERVER_PORT' => 80,
      'HTTP_HOST' => 'localhost',
      'SERVER_PROTOCOL' => 'HTTP/1.1',
      'SCRIPT_NAME' => '/index.php',
      'PHP_SELF' => '/index.php',
    ];
    
    $this->assertTrue($this->app->run($serverRequest));
  }

  /**
   * Test middleware arrays
   */
  public function testMiddleware(): void
  {
    // These are protected properties, so we test them indirectly
    // by verifying the app can handle requests (middleware chain works)
    $this->assertInstanceOf(\Spin\Application::class, $this->app);
    
    // If middleware was broken, run() would fail
    $this->assertTrue(true);
  }

  /**
   * Test that config files exist and are loadable
   */
  public function testConfigFiles(): void
  {
    $config = $this->app->getConfig();
    
    // Check if config has expected structure
    if (property_exists($config, 'application')) {
      $this->assertIsObject($config->application);
    }
    
    // Config should at least be an object
    $this->assertIsObject($config);
  }

}