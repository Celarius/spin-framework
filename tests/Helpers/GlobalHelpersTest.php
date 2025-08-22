<?php declare(strict_types=1);

namespace Spin\tests\Helpers;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * Test Global Helper Functions
 * 
 * Tests the global helper functions defined in src/Helpers.php
 * These functions provide convenient shortcuts for common framework operations.
 */
class GlobalHelpersTest extends TestCase
{
    /**
     * @var \Spin\Application
     */
    protected \Spin\Application $app;

    /**
     * Setup test environment
     */
    public function setUp(): void
    {
        global $app;
        $this->app = $app;
    }

    /**
     * Test env() function
     */
    public function testEnv(): void
    {
        // Test with existing environment variable
        putenv('TEST_VAR=test_value');
        $this->assertEquals('test_value', env('TEST_VAR'));
        
        // Test with default value for non-existent variable
        $this->assertEquals('default_value', env('NON_EXISTENT_VAR', 'default_value'));
        
        // Test boolean values
        putenv('BOOL_TRUE=true');
        putenv('BOOL_FALSE=false');
        $this->assertTrue(env('BOOL_TRUE'));
        $this->assertFalse(env('BOOL_FALSE'));
        
        // Test null and empty values
        putenv('NULL_VAL=null');
        putenv('EMPTY_VAL=empty');
        $this->assertNull(env('NULL_VAL'));
        $this->assertEquals('', env('EMPTY_VAL'));
        
        // Test quoted values
        putenv('QUOTED_VAL="quoted_string"');
        $this->assertEquals('quoted_string', env('QUOTED_VAL'));
        
        // Clean up
        putenv('TEST_VAR');
        putenv('BOOL_TRUE');
        putenv('BOOL_FALSE');
        putenv('NULL_VAL');
        putenv('EMPTY_VAL');
        putenv('QUOTED_VAL');
    }

    /**
     * Test app() function
     */
    public function testApp(): void
    {
        // Test getting the app object
        $this->assertSame($this->app, app());
        
        // Test getting a property (this should work with the real app)
        $this->assertNotNull(app('config'));
    }

    /**
     * Test config() function
     */
    public function testConfig(): void
    {
        // Test getting config object
        $config = config();
        $this->assertNotNull($config);
        
        // Test getting a config value (if any exists)
        $appName = config('application.name');
        // This might be null if no config is loaded, but shouldn't throw an error
        $this->assertTrue(true); // Just ensure no exception is thrown
    }

    /**
     * Test container() function
     */
    public function testContainer(): void
    {
        // Test getting container
        $container = container();
        $this->assertNotNull($container);
    }

    /**
     * Test logger() function
     */
    public function testLogger(): void
    {
        $logger = logger();
        $this->assertNotNull($logger);
    }

    /**
     * Test getRequest() function
     */
    public function testGetRequest(): void
    {
        $request = getRequest();
        $this->assertInstanceOf(Request::class, $request);
    }

    /**
     * Test getResponse() function
     */
    public function testGetResponse(): void
    {
        $response = getResponse();
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * Test queryParam() function
     */
    public function testQueryParam(): void
    {
        // Test getting non-existent parameter with default
        $this->assertEquals('default_value', queryParam('non_existent', 'default_value'));
        
        // Test getting non-existent parameter without default
        $this->assertNull(queryParam('non_existent'));
    }

    /**
     * Test queryParams() function
     */
    public function testQueryParams(): void
    {
        // Test getting all query parameters
        $params = queryParams();
        $this->assertIsArray($params);
        // This might be empty if no query string, but should be an array
    }

    /**
     * Test postParam() function
     */
    public function testPostParam(): void
    {
        // Test getting non-existent parameter with default
        $this->assertEquals('default_value', postParam('non_existent', 'default_value'));
        
        // Test getting non-existent parameter without default
        $this->assertNull(postParam('non_existent'));
    }

    /**
     * Test postParams() function
     */
    public function testPostParams(): void
    {
        // Test getting all POST parameters
        $params = postParams();
        $this->assertIsArray($params);
        // This might be empty if no POST data, but should be an array
    }

    /**
     * Test cookieParam() function
     */
    public function testCookieParam(): void
    {
        // Test getting non-existent cookie with default
        $this->assertEquals('default_value', cookieParam('non_existent', 'default_value'));
        
        // Test getting non-existent cookie without default
        $this->assertNull(cookieParam('non_existent'));
    }

    /**
     * Test cookieParams() function
     */
    public function testCookieParams(): void
    {
        // Test getting all cookies
        $cookies = cookieParams();
        $this->assertIsArray($cookies);
        // This might be empty if no cookies, but should be an array
    }

    /**
     * Test redirect() function
     */
    public function testRedirect(): void
    {
        $result = redirect('http://example.com', 301, ['X-Custom-Header' => 'value']);
        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(301, $result->getStatusCode());
        $this->assertEquals('http://example.com', $result->getHeaderLine('Location'));
        $this->assertEquals('value', $result->getHeaderLine('X-Custom-Header'));
    }

    /**
     * Test response() function
     */
    public function testResponse(): void
    {
        $result = response('Test body', 201, ['X-Custom-Header' => 'value']);
        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(201, $result->getStatusCode());
        $this->assertEquals('Test body', (string) $result->getBody());
        $this->assertEquals('value', $result->getHeaderLine('X-Custom-Header'));
    }

    /**
     * Test responseJson() function
     */
    public function testResponseJson(): void
    {
        $data = ['message' => 'Success', 'status' => 'ok'];
        $result = responseJson($data, 200, JSON_PRETTY_PRINT, ['X-Custom-Header' => 'value']);
        
        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals('application/json', $result->getHeaderLine('Content-Type'));
        $this->assertEquals('value', $result->getHeaderLine('X-Custom-Header'));
        
        // Verify JSON content
        $body = (string) $result->getBody();
        $decoded = json_decode($body, true);
        $this->assertEquals($data, $decoded);
    }

    /**
     * Test responseJson() with invalid data
     */
    public function testResponseJsonInvalidData(): void
    {
        // Create data that can't be JSON encoded (resource)
        $resource = fopen('php://temp', 'r');
        $result = responseJson(['resource' => $resource], 200);
        
        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals('', (string) $result->getBody());
        
        fclose($resource);
    }

    /**
     * Test responseXml() function
     */
    public function testResponseXml(): void
    {
        $data = ['item' => ['name' => 'Test', 'value' => '123']];
        $result = responseXml($data, 'root', 200, ['X-Custom-Header' => 'value']);
        
        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals('application/xml', $result->getHeaderLine('Content-Type'));
        $this->assertEquals('value', $result->getHeaderLine('X-Custom-Header'));
        
        // Verify XML content
        $body = (string) $result->getBody();
        $this->assertStringContainsString('<root>', $body);
        $this->assertStringContainsString('<item>', $body);
        $this->assertStringContainsString('<name>Test</name>', $body);
    }

    /**
     * Test responseHtml() function
     */
    public function testResponseHtml(): void
    {
        $result = responseHtml('<h1>Hello World</h1>', 200, ['X-Custom-Header' => 'value']);
        
        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals('text/html', $result->getHeaderLine('Content-Type'));
        $this->assertEquals('value', $result->getHeaderLine('X-Custom-Header'));
        $this->assertEquals('<h1>Hello World</h1>', (string) $result->getBody());
    }

    /**
     * Test responseFile() function
     */
    public function testResponseFile(): void
    {
        // Create a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'Test file content');
        
        $result = responseFile($tempFile, 200, ['X-Custom-Header' => 'value'], true);
        
        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals('value', $result->getHeaderLine('X-Custom-Header'));
        
        // Clean up
        unlink($tempFile);
    }

    /**
     * Test getClientIp() function
     */
    public function testGetClientIp(): void
    {
        // Test with HTTP_CLIENT_IP
        $_SERVER['HTTP_CLIENT_IP'] = '192.168.1.100';
        $this->assertEquals('192.168.1.100', getClientIp());
        
        // Test with HTTP_X_FORWARDED_FOR
        unset($_SERVER['HTTP_CLIENT_IP']);
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '10.0.0.1';
        $this->assertEquals('10.0.0.1', getClientIp());
        
        // Test with REMOTE_ADDR
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        $_SERVER['REMOTE_ADDR'] = '172.16.0.1';
        $this->assertEquals('172.16.0.1', getClientIp());
        
        // Test with invalid IP
        $_SERVER['REMOTE_ADDR'] = 'invalid_ip';
        $this->assertEquals('0.0.0.0', getClientIp());
        
        // Test with no IP
        unset($_SERVER['REMOTE_ADDR']);
        $this->assertEquals('0.0.0.0', getClientIp());
    }

    /**
     * Test generateRefId() function
     */
    public function testGenerateRefId(): void
    {
        // Test without prefix
        $refId1 = generateRefId();
        $this->assertIsString($refId1);
        $this->assertGreaterThan(0, strlen($refId1));
        
        // Test with prefix
        $refId2 = generateRefId('TEST_');
        $this->assertStringStartsWith('TEST_', $refId2);
        
        // Test uniqueness (with a small delay to ensure different timestamps)
        usleep(1000); // 1ms delay
        $refId3 = generateRefId();
        $this->assertNotEquals($refId1, $refId3);
    }

    /**
     * Test getConfigPath() function
     */
    public function testGetConfigPath(): void
    {
        $configPath = getConfigPath();
        $this->assertIsString($configPath);
        $this->assertStringEndsWith('Config', $configPath);
    }

    /**
     * Test mime_content_type() function
     */
    public function testMimeContentType(): void
    {
        // Since mime_content_type is a built-in PHP function, test our custom mime_content_type_ex instead
        // Test with known extension
        $this->assertEquals('text/html', mime_content_type_ex('test.html'));
        $this->assertEquals('application/json', mime_content_type_ex('data.json'));
        $this->assertEquals('image/png', mime_content_type_ex('image.png'));
        
        // Test with unknown extension
        $this->assertEquals('application/octet-stream', mime_content_type_ex('unknown.xyz'));
    }

    /**
     * Test mime_content_type_ex() function
     */
    public function testMimeContentTypeEx(): void
    {
        // Test with known extensions
        $this->assertEquals('text/html', mime_content_type_ex('test.html'));
        $this->assertEquals('application/json', mime_content_type_ex('data.json'));
        $this->assertEquals('image/png', mime_content_type_ex('image.png'));
        
        // Test with unknown extension
        $this->assertEquals('application/octet-stream', mime_content_type_ex('unknown.xyz'));
        
        // Test with mixed case
        $this->assertEquals('text/html', mime_content_type_ex('test.HTML'));
        $this->assertEquals('application/json', mime_content_type_ex('data.JSON'));
    }

    /**
     * Clean up after tests
     */
    public function tearDown(): void
    {
        // Clean up server variables
        unset($_SERVER['HTTP_CLIENT_IP']);
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        unset($_SERVER['REMOTE_ADDR']);
    }
}
