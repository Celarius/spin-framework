<?php declare(strict_types=1);

/**
 * Uploaded files manager
 *
 * @package  Spin
 *
 * @link http://php.net/manual/en/reserved.variables.files.php
 * @link http://php.net/manual/en/features.file-upload.post-method.php
 * @link http://php.net/manual/en/features.file-upload.php
 */

namespace Spin\Core;

use \Spin\Core\AbstractBaseClass;
use \Spin\Core\Config;
use \Spin\Core\Logger;
use \Spin\Core\UploadedFile;
use \Spin\Core\UploadedFilesManagerInterface;
use \Spin\Exceptions\SpinException;

use \Psr\Http\Message\Response;

class UploadedFilesManager extends AbstractBaseClass implements UploadedFilesManagerInterface
{
  /** @var       array          Array with \Spin\Core\File objects  */
  protected $files;

  /**
   * Constructor
   *
   * @param      array  $files  The files array. Usually $_FILES global
   */
  public function __construct(array $files)
  {
    parent::__construct();

    # Empty array
    $this->files = [];

    # Parse the $_FILES array
    $this->parseFiles($_FILES);
  }

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
  public function parseFiles(array $files, int $maxSize=0)
  {
    $list = [];

    # Loop all Parameter names
    foreach ($files as $param => $fileInfo)
    {
      if ( \is_array($fileInfo['name']) ) {
        # Loop each index number
        foreach ($fileInfo['name'] as $i=>$value)
        {
          $file['name'] = $fileInfo['name'][$i];
          $file['type'] = $fileInfo['type'][$i];
          $file['tmp_name'] = $fileInfo['tmp_name'][$i];
          $file['error'] = $fileInfo['error'][$i];
          $file['size'] = $fileInfo['size'][$i];
          $file['param'] = $param;

          $list[] = $file;
        }

      } else {
        $file = $fileInfo;
        $file['param'] = $param;

        $list[] = $file;
      }
    }

    # For each entry in the list, add a file object
    foreach ($list as $file)
    {
      $this->files[] = new UploadedFile($file);
    }

    # Validate/check files/values
    $this->validateInput($maxSize);

    return \count($this->files);
  }

  /**
   * Validates the $files array, making sure all is OK
   *
   * @param      array             $files    The $_FILES array
   * @param      integer           $maxSize  The maximum size of an uploaded file. 0=Ignore
   *
   * @throws     Exception
   *
   * @return     boolean
   */
  protected function validateInput(int $maxSize=0)
  {
    if (0 == $maxSize) $maxSize = $this->getMaximumFileUploadSize();

    foreach ($this->files as $file)
    {
      # Filesize check
      if ( $maxSize>0 && $file['size']>$maxSize) {
        throw new SpinException('Exceeded filesize limit');
      }

      # Check error codes (http://php.net/manual/en/features.file-upload.errors.php)
      switch ( $file['error'] ) {
        case \UPLOAD_ERR_OK:
            break;

        case \UPLOAD_ERR_PARTIAL:
            throw new SpinException($file['name'] . ': The uploaded file was only partially uploaded');

        case \UPLOAD_ERR_NO_FILE:
            throw new SpinException($file['name'] . ': No file was uploaded');

        case \UPLOAD_ERR_INI_SIZE:
            throw new SpinException($file['name'] . ': The uploaded file exceeds the upload_max_filesize directive in php.ini');

        case \UPLOAD_ERR_FORM_SIZE:
            throw new SpinException($file['name'] . ': The uploaded file exceeds MAX_FILE_SIZE (' . $maxSize . ') ');

        case \UPLOAD_ERR_NO_TMP_DIR:
            throw new SpinException($file['name'] . ': Missing a temporary folder');

        case \UPLOAD_ERR_CANT_WRITE:
            throw new SpinException($file['name'] . ': Failed to write file to disk');

        default:
            throw new SpinException('Unknown errors');
      }
    }

    return true;
  }

  /**
   * This function returns the maximum files size that can be uploaded in PHP
   *
   * @link       https://stackoverflow.com/questions/13076480/php-get-actual-maximum-upload-size
   *
   * @return     int   File size in bytes
   */
  protected function getMaximumFileUploadSize()
  {
    return \min($this->convertPHPSizeToBytes(\ini_get('post_max_size')), $this->convertPHPSizeToBytes(\ini_get('upload_max_filesize')));
  }

  /**
   * This function transforms the php.ini notation for numbers (like '2M') to an
   * integer (2*1024*1024 in this case)
   *
   * @param      string   $sSize
   *
   * @return     integer  The value in bytes
   */
  protected function convertPHPSizeToBytes($sSize)
  {
    $sSuffix = \strtoupper(substr($sSize, -1));

    if (!\in_array($sSuffix,array('P','T','G','M','K'))){
      return (int)$sSize;
    }

    $iValue = \substr($sSize, 0, -1);

    switch ($sSuffix) {
      case 'P':
        $iValue *= 1024;
        // Fallthrough intended
      case 'T':
        $iValue *= 1024;
        // Fallthrough intended
      case 'G':
        $iValue *= 1024;
        // Fallthrough intended
      case 'M':
        $iValue *= 1024;
        // Fallthrough intended
      case 'K':
        $iValue *= 1024;
        break;
    }

    return (int)$iValue;
  }

  /**
   * Gets the files
   *
   * @return     array  The files
   */
  public function getFiles(): array
  {
    return $this->files;
  }
}
