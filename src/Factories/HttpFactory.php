<?php declare(strict_types=1);

/**
 * HTTP Factory
 *
 * This factory produces PSR-7 compliant objects using
 * the Guzzle framework.
 *
 * @link     https://github.com/guzzle/guzzle
 * @package  Spin
 */

namespace Spin\Factories;

use \InvalidArgumentException;
use \Spin\Factories\AbstractFactory;

// Guzzle
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

// PSR-7
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

// PSR-17
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class HttpFactory
  extends
    AbstractFactory
  implements
    ServerRequestFactoryInterface,
    RequestFactoryInterface,
    ResponseFactoryInterface
{
  /**
   * Create a new request.
   *
   * @param string $method
   * @param UriInterface|string $uri
   *
   * @return RequestInterface
   */
  public function createRequest($method, $uri)
  {
    $request = new Request($method, $uri);

    log()->debug('Created PSR-7 Request (Guzzle)';

    return $request;
  }

  /**
   * Create a new response.
   *
   * @param integer $code HTTP status code
   *
   * @return ResponseInterface
   */
  public function createResponse($code = 200)
  {
    $response = new Response($code);

    log()->debug('Created PSR-7 Response (Guzzle)';

    return $response;
  }

  /**
   * Create a new server request.
   *
   * @param string $method
   * @param UriInterface|string $uri
   *
   * @return ServerRequestInterface
   */
  public function createServerRequest($method, $uri)
  {
    # Copied from Guzzles ::fromGlobals(), but we need to support the $server array as
    # paramter, so we use that instead of the $_SERVER array guzzle uses by default

    $method = isset($server['REQUEST_METHOD']) ? $server['REQUEST_METHOD'] : 'GET';
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $uri = $uri;
    $body = new LazyOpenStream('php://input', 'r+');
    $protocol = isset($server['SERVER_PROTOCOL']) ? str_replace('HTTP/', '', $server['SERVER_PROTOCOL']) : '1.1';

    $serverRequest = new ServerRequest($method, $uri, $headers, $body, $protocol, $server);

    log()->debug('Created PSR-7 ServerRequest("'.$method.'","'.$url.'") (Guzzle)');

    return $serverRequest
        ->withCookieParams($_COOKIE)
        ->withQueryParams($_GET)
        ->withParsedBody($_POST)
        ->withUploadedFiles(\GuzzleHttp\Psr7\ServerRequest::normalizeFiles($_FILES));
  }

  /**
   * Create a new server request from server variables.
   *
   * @param array $server Typically $_SERVER or similar structure.
   *
   * @return ServerRequestInterface
   *
   * @throws \InvalidArgumentException
   *  If no valid method or URI can be determined.
   */
  public function createServerRequestFromArray(array $server)
  {
    # Copied from Guzzles ::fromGlobals(), but we need to support the $server array as
    # paramter, so we use that instead of the $_SERVER array guzzle uses by default

    $method = isset($server['REQUEST_METHOD']) ? $server['REQUEST_METHOD'] : 'GET';
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $uri = \GuzzleHttp\Psr7\ServerRequest::getUriFromGlobals();
    $body = new LazyOpenStream('php://input', 'r+');
    $protocol = isset($server['SERVER_PROTOCOL']) ? str_replace('HTTP/', '', $server['SERVER_PROTOCOL']) : '1.1';

    $serverRequest = new ServerRequest($method, $uri, $headers, $body, $protocol, $server);

    log()->debug('Created PSR-7 ServerRequest from array (Guzzle)');

    return $serverRequest
        ->withCookieParams($_COOKIE)
        ->withQueryParams($_GET)
        ->withParsedBody($_POST)
        ->withUploadedFiles(\GuzzleHttp\Psr7\ServerRequest::normalizeFiles($_FILES));
  }

}
