<?php declare(strict_types=1);

/**
 * Uploaded Files Manager Interface
 *
 * Defines the contract for uploaded files manager implementations. Specifies
 * methods for parsing file upload structures and managing collections of
 * uploaded files. Implemented by UploadedFilesManager to provide framework
 * file upload management capabilities.
 *
 * @package  Spin\Core
 * @author   Spin Framework Team
 * @since    1.0.0
 */

namespace Spin\Core;

interface UploadedFilesManagerInterface
{
  /**
   * Parse the $files structure
   *
   * Adds Spin\Core\File objects to $this->files array
   *
   * @param      array    $files    The files. Usually $_FILES global
   * @param      integer  $maxSize  The maximum size. 0=Use php.ini defaults
   *
   * @return     int      Count of files added to structure
   */
  public function parseFiles(array $files, int $maxSize=0);

  /**
   * Gets the files
   *
   * @return     array  The files
   */
  public function getFiles(): array;
}
