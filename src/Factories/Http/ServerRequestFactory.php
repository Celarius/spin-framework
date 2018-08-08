<?php declare(strict_types=1);

/**
 * HTTP ServerRequest Factory
 *
 * This factory produces PSR-7 compliant objects using
 * the Guzzle framework.
 *
 * @link     https://github.com/guzzle/guzzle
 * @package  Spin
 */

namespace Spin\Factories\Http;

use \InvalidArgumentException;
use \Spin\Factories\AbstractFactory;

# Guzzle
use \GuzzleHttp\Psr7\Request;
use \GuzzleHttp\Psr7\ServerRequest;
use \GuzzleHttp\Psr7\LazyOpenStream;

# PSR-7
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ServerRequestInterface;

# PSR-17
use \Psr\Http\Message\ServerRequestFactoryInterface;


class ServerRequestFactory extends AbstractFactory implements ServerRequestFactoryInterface
{
  /**
   * Create a new server request
   *
   * @param      string                  $method
   * @param      UriInterface|string     $uri
   * @param      array                   $serverParams  The server parameters
   *
   * @return     ServerRequestInterface
   */
  public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
  {
    # Copied from Guzzles ::fromGlobals(), but we need to support the $server array as
    # paramter, so we use that instead of the $_SERVER array guzzle uses by default

    $method = isset($server['REQUEST_METHOD']) ? $server['REQUEST_METHOD'] : 'GET';
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $uri = $uri;
    $body = new LazyOpenStream('php://input', 'r+');
    $protocol = isset($server['SERVER_PROTOCOL']) ? str_replace('HTTP/', '', $server['SERVER_PROTOCOL']) : '1.1';

    $serverRequest = new ServerRequest($method, $uri, $headers, $body, $protocol, $server);

    logger()->debug('Created PSR-7 ServerRequest("'.$method.'","'.$url.'") (Guzzle)');

    return $serverRequest
        ->withCookieParams($_COOKIE)
        ->withQueryParams($_GET)
        ->withParsedBody($_POST)
        ->withUploadedFiles(ServerRequest::normalizeFiles($_FILES));
  }

  /**
   * Create a new server request from server variables array
   *
   * @param      array                   $server  Typically $_SERVER or similar
   *                                              array
   *
   * @return     ServerRequestInterface
   *
   * @throws     \InvalidArgumentException  If no valid method or URI can be determined.
   */
  public function createServerRequestFromArray(?array $server)
  {
    global $app;

    # Copied from Guzzles ::fromGlobals(), but we need to support the $server array as
    # paramter, so we use that instead of the $_SERVER array guzzle uses by default

    $method = isset($server['REQUEST_METHOD']) ? $server['REQUEST_METHOD'] : 'GET';
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $uri = ServerRequest::getUriFromGlobals();
    $body = new LazyOpenStream('php://input', 'r+');
    $protocol = isset($server['SERVER_PROTOCOL']) ? str_replace('HTTP/', '', $server['SERVER_PROTOCOL']) : '1.1';

    $serverRequest = new ServerRequest($method, $uri, $headers, $body, $protocol, $server);

    $app->getLogger()->debug('Created PSR-7 ServerRequest("'.$method.'","'.$uri.'") from array (Guzzle)');

    return $serverRequest
           ->withCookieParams($_COOKIE)
           ->withQueryParams($_GET)
           ->withParsedBody($_POST)
           ->withUploadedFiles(ServerRequest::normalizeFiles($_FILES));
  }

}
