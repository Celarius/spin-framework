<?php declare(strict_types=1);

/**
 * UploadedFile
 *
 * @package  Spin
 */

namespace Spin\Core;

use \Spin\Core\AbstractBaseClass;
use \Spin\Core\FileInterface;

class UploadedFile extends AbstractBaseClass implements UploadedFileInterface
{
  /** @var        string          The Parameter name on the form */
  protected $parameter;

  /** @var        string          Filename */
  protected $name;

  /** @var        string          File MIME type */
  protected $type;

  /** @var        string          Temporary filename (in temp path) */
  protected $tmp_name;

  /** @var        int             Error code */
  protected $error;

  /** @var        int             Byte size of file */
  protected $size;

  /** @var        string          Real filename once the file has been moved */
  protected $filename;

  /** @var        boolean         False until a successful move() */
  protected $isMoved;

  /**
   * Constructor
   *
   * @param      array  $file   Array with file data
   */
  public function __construct(array $file)
  {
    parent::__construct();

    # Populate properties
    $this->setName($file['name'] ?? '');
    $this->setType($file['type'] ?? '');
    $this->setTmpName($file['tmp_name'] ?? '');
    $this->setError($file['error'] ?? 0);
    $this->setSize($file['size'] ?? 0);
    $this->setFilename($file['filename'] ?? '');

    $this->setIsMoved(false);
  }

  /**
   * Move a downloaded file to the destination $directory
   *
   * @param      string   $directory  The directory to move the file to
   * @param      string   $filename   Optional. The new filename
   *
   * @return     boolean
   */
  public function move(string $directory, string $filename='')
  {
    # If no filename is provided, assume the incoming name
    if (empty($filename)) $this->setFilename( $this->getName() );

    # Move the file
    $ok = @\move_uploaded_file($this->getTmpName(), $directory . DIRECTORY_SEPARATOR . $filename );

    # Set property
    $this->setIsMoved($ok);

    return $ok;
  }

  /**
   * Copy a downloaded file to $directory named $filename
   *
   * @param      string   $directory  The directory to move the file to
   * @param      string   $filename   Optional. The new filename
   *
   * @return     boolean
   */
  public function copy(string $directory, string $filename='')
  {
    # If no filename is provided, assume the incoming name
    if (empty($filename)) $this->setFilename( $this->getName() );

    # Move the file
    $ok = @\copy($this->getTmpName(), $directory . DIRECTORY_SEPARATOR . $filename );

    return $ok;
  }

  /**
   * Gets the MimeType
   *
   * @return     string
   */
  public function getMimeType()
  {
    return $this->getType();
  }

  /**
   * Gets the name.
   *
   * @return     string
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Sets the name.
   *
   * @param      string  $name
   *
   * @return     self
   */
  public function setName(string $name)
  {
    $this->name = $name;

    return $this;
  }

  /**
   * Gets the type (Mime Type).
   *
   * @return     string
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Sets the type (Mime Type)
   *
   * @param      mixed  $type
   *
   * @return     self
   */
  public function setType(string $type)
  {
    $this->type = $type;

    return $this;
  }

  /**
   * Gets the temporary name.
   *
   * @return     string
   */
  public function getTmpName()
  {
    return $this->tmp_name;
  }

  /**
   * Sets the temporary name.
   *
   * @param      mixed  $tmp_name
   *
   * @return     self
   */
  public function setTmpName(string $tmp_name)
  {
    $this->tmp_name = $tmp_name;

    return $this;
  }

  /**
   * Gets the error.
   *
   * @return     int
   */
  public function getError()
  {
    return $this->error;
  }

  /**
   * Sets the error.
   *
   * @param      int  $error
   *
   * @return     self
   */
  public function setError(int $error)
  {
    $this->error = $error;

    return $this;
  }

  /**
   * Gets the size.
   *
   * @return     int
   */
  public function getSize()
  {
    return $this->size;
  }

  /**
   * Sets the size.
   *
   * @param      mixed  $size
   *
   * @return     self
   */
  public function setSize(int $size)
  {
    $this->size = $size;

    return $this;
  }

  /**
   * Gets the filename.
   *
   * @return     string
   */
  public function getFilename()
  {
    return $this->filename;
  }

  /**
   * Sets the filename.
   *
   * @param      mixed  $filename
   *
   * @return     self
   */
  public function setFilename(string $filename)
  {
    $this->filename = $filename;

    return $this;
  }

  /**
   * Gets the isMoved.
   *
   * @return     bool
   */
  public function getIsMoved()
  {
    return $this->isMoved;
  }

  /**
   * Sets the isMoved.
   *
   * @param      mixed  $isMoved
   *
   * @return     self
   */
  public function setIsMoved(bool $isMoved)
  {
    $this->isMoved = $isMoved;

    return $this;
  }
}
