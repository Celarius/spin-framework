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

// Guzzle
use GuzzleHttp\Psr7\LazyOpenStream;

// PSR-7
use Psr\Http\Message\StreamInterface;

// PSR-17
use Psr\Http\Message\StreamFactoryInterface;

class StreamFactory extends AbstractFactory implements StreamFactoryInterface
{
  /**
   * Return a StreamObject for a string
   *
   * @param  string $for [description]
   *
   * @return object
   */
  public function createStream(string $for)
  {
    return \GuzzleHttp\Psr7\stream_for($for);
  }

  /**
   * Create a stream from an existing file.
   *
   * The file MUST be opened using the given mode, which may be any mode
   * supported by the `fopen` function.
   *
   * The `$filename` MAY be any string supported by `fopen()`.
   *
   * @param string $filename
   * @param string $mode
   *
   * @return StreamInterface
   */
  public function createStreamFromFile($filename, $mode = 'r')
  {
    # Open the file
    $resource = fopen($filename, $mode);

    return \GuzzleHttp\Psr7\stream_for($resource);
  }

  /**
   * Create a new stream from an existing resource.
   *
   * The stream MUST be readable and may be writable.
   *
   * @param resource $resource
   *
   * @return StreamInterface
   */
  public function createStreamFromResource($resource)
  {
    return \GuzzleHttp\Psr7\stream_for($resource);
  }

}
