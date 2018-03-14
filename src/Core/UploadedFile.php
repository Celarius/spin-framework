<?php declare(strict_types=1);

namespace Spin\Core;

use Spin\Core\AbstractBaseClass;
use Spin\Core\FilesInterface;

class UploadedFile extends AbstractBaseClass implements FilesInterface
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

  /**
   * Constructor
   *
   * @param      array  $file     Array with file data
   */
  public function __construct(array $file)
  {
    parent::__construct();

    # Populate properties
    $this->SetName($file['name'] ?? '');
    $this->SetType($file['type'] ?? '');
    $this->SetTmpName($file['tmp_name'] ?? '');
    $this->SetError($file['error'] ?? null);
    $this->SetSize($file['size'] ?? 0);
    $this->SetFilename($file['filename'] ?? '');
  }

  /**
   * Gets the name.
   *
   * @return     mixed
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Sets the name.
   *
   * @param      mixed  $name
   *
   * @return     self
   */
  public function setName($name)
  {
    $this->name = $name;

    return $this;
  }

  /**
   * Gets the type.
   *
   * @return     mixed
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Sets the type.
   *
   * @param      mixed  $type
   *
   * @return     self
   */
  public function setType($type)
  {
    $this->type = $type;

    return $this;
  }

  /**
   * Gets the temporary name.
   *
   * @return     mixed
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
  public function setTmpName($tmp_name)
  {
    $this->tmp_name = $tmp_name;

    return $this;
  }

  /**
   * Gets the error.
   *
   * @return     mixed
   */
  public function getError()
  {
    return $this->error;
  }

  /**
   * Sets the error.
   *
   * @param      mixed  $error
   *
   * @return     self
   */
  public function setError($error)
  {
    $this->error = $error;

    return $this;
  }

  /**
   * Gets the size.
   *
   * @return     mixed
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
  public function setSize($size)
  {
    $this->size = $size;

    return $this;
  }

  /**
   * Gets the filename.
   *
   * @return     mixed
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
  public function setFilename($filename)
  {
    $this->filename = $filename;

    return $this;
  }
}
