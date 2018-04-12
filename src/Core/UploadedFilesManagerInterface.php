<?php declare(strict_types=1);

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

}
