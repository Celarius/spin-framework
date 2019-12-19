<?php declare(strict_types=1);

/**
 * UploadedFileInterface
 *
 * @package  Spin
 */

namespace Spin\Core;

interface UploadedFileInterface
{
  /**
   * Move a downloaded file to the destination $directory
   *
   * @param      string   $directory  The directory to move the file to
   * @param      string   $filename   Optional. The new filename
   *
   * @return     boolean
   */
  public function move(string $directory, string $filename='');

}
