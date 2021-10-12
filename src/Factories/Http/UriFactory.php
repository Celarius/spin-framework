<?php declare(strict_types=1);

/**
 * HTTP Uri Factory
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
use \GuzzleHttp\Psr7\LazyOpenStream;

# PSR-7
use Psr\Http\Message\UriFactoryInterface;

class UriFactory extends AbstractFactory implements UriFactoryInterface
{
  /**
   * Create a new URI.
   *
   * @param      string        $uri
   *
   * @return     UriInterface
   *
   * @throws     \InvalidArgumentException  If the given URI cannot be parsed.
   */
  public function createUri(string $uri = ''): UriInterface
  {
    return \GuzzleHttp\Psr7\uri_for($uri);
  }
}
