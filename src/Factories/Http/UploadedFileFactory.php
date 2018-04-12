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

# PSR-17
// use Psr\Http\Message\UploadedFileFactoryInterface;
use \Interop\Http\Factory\UploadedFileFactoryInterface;

class UploadedFileFactory extends AbstractFactory implements UploadedFileFactoryInterface
{
  /**
   * Create a new uploaded file.
   *
   * If a string is used to create the file, a temporary resource will be
   * created with the content of the string.
   *
   * If a size is not provided it will be determined by checking the size of the
   * file.
   *
   * @see        http://php.net/manual/features.file-upload.post-method.php
   * @see        http://php.net/manual/features.file-upload.errors.php
   *
   * @param      string|resource        $file
   * @param      integer                $size             in bytes
   * @param      integer                $error            PHP file upload error
   * @param      string                 $clientFilename
   * @param      string                 $clientMediaType
   *
   * @return     UploadedFileInterface
   *
   * @throws     \InvalidArgumentException  If the file resource is not readable.
   */
  public function createUploadedFile(
      $file,
      $size = null,
      $error = \UPLOAD_ERR_OK,
      $clientFilename = null,
      $clientMediaType = null
  ) {
    return null;
  }

}
