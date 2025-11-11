<?php declare(strict_types=1);

/**
 * Uploaded File Interface
 *
 * Defines the contract for uploaded file implementations. Specifies methods
 * for managing file uploads including moving files to destination directories.
 * Implemented by UploadedFile to provide framework file upload capabilities.
 *
 * @package  Spin\Core
 * @author   Spin Framework Team
 * @since    1.0.0
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
