<?php declare(strict_types=1);

/**
 * HTTP Request Factory
 *
 * This factory produces PSR-7 compliant objects using
 * the Guzzle framework.
 *
 * @package  Spin
 * @link     https://github.com/guzzle/guzzle
 */

namespace Spin\Factories\Http;

use \InvalidArgumentException;
use \Spin\Factories\AbstractFactory;

# PSR-7
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\UriInterface;

# PSR-17
use \Psr\Http\Message\RequestFactoryInterface;

# Guzzle
use \GuzzleHttp\Psr7\Request;

class RequestFactory extends AbstractFactory implements RequestFactoryInterface
{
  /**
   * Create a new request.
   *
   * @param      string               $method
   * @param      UriInterface|string  $uri
   *
   * @return     RequestInterface
   */
  public function createRequest(string $method, $uri): RequestInterface
  {
    $request = new Request($method, $uri);

    \logger()->debug('Created PSR-7 Request("'.$method.'","'.$uri.'"") (Guzzle)');

    return $request;
  }


}
