<?php declare(strict_types=1);

/**
 * HTTP Response Factory
 *
 * This factory produces PSR-7 compliant objects using
 * the Guzzle framework.
 *
 * @link     https://github.com/guzzle/guzzle
 * @package  Spin
 */

namespace Spin\Factories\Http;

use InvalidArgumentException;
use Spin\Factories\AbstractFactory;

# Guzzle
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\LazyOpenStream;

# PSR-7
use Psr\Http\Message\ResponseInterface;

# PSR-17
// use Psr\Http\Message\ResponseFactoryInterface;
use Interop\Http\Factory\ResponseFactoryInterface;

class ResponseFactory extends AbstractFactory implements ResponseFactoryInterface
{
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

    logger()->debug('Created PSR-7 Response (Guzzle)');

    return $response;
  }

}
