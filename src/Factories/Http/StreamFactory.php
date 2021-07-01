<?php declare(strict_types=1);

/**
 * HTTP Stream Factory
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
use \GuzzleHttp\Psr7\Utils;

# PSR-7
use \Psr\Http\Message\StreamInterface;
use \Psr\Http\Message\UtilsInterface;

# PSR-17
use Psr\Http\Message\StreamFactoryInterface;

class StreamFactory extends AbstractFactory implements StreamFactoryInterface
{
  /**
   * Return a StreamObject for a string
   *
   * @param      string  $content  The content
   *
   * @return     object
   */
  public function createStream(string $content = ''): StreamInterface
  {
    return Utils::streamFor($content);
  }

  /**
   * Create a stream from an existing file.
   *
   * The file MUST be opened using the given mode, which may be any mode
   * supported by the `fopen` function.
   *
   * The `$filename` MAY be any string supported by `fopen()`.
   *
   * @param      string           $filename
   * @param      string           $mode
   *
   * @return     StreamInterface
   */
  public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
  {
    # Open the file
    $resource = \fopen($filename, $mode);

    return Utils::streamFor($resource);
  }

  /**
   * Create a new stream from an existing resource.
   *
   * The stream MUST be readable and may be writable.
   *
   * @param      resource         $resource
   *
   * @return     StreamInterface
   */
  public function createStreamFromResource($resource): StreamInterface
  {
    return Utils::streamFor($resource);
  }

}
