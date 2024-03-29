<?php declare(strict_types=1);

/**
 * PDO SqLite Connection driver
 *
 * https://www.sqlite.org
 * 
 * @package    Spin
 */

namespace Spin\Database\Drivers\Pdo;

use Spin\Database\PdoConnection;

class SqLite extends PdoConnection
{
  /** @var string SQLite DB Filename */
  protected $filename;

  /**
   * Constructor
   *
   * @param      string  $connectionName  [description]
   * @param      array   $params          [description]
   */
  public function __construct(string $connectionName, array $params=[])
  {
    # Set Driver name
    $this->setDriver('sqlite');

    # Sqlite has it's own $filename property, extract it
    $this->setFilename($params['filename'] ?? '');

    # Username & Password
    $this->setUsername('');
    $this->setPassword('');

    # Parent Constructor
    parent::__construct($connectionName,$params);
  }

  /**
   * Get DSN - SqLite formatting
   *
   * sqlite:/tmp/foo.db
   *
   * @return     string  [description]
   */
  public function getDsn(): string
  {
/*
  # File connection
  $connection = new PDO('sqlite:<filename>', null, null
    array(PDO::ATTR_PERSISTENT => true)
  );

  # Memory connection
  $connection = new PDO('sqlite::memory:', null, null
    array(PDO::ATTR_PERSISTENT => true)
  );
*/
    # Build the DSN
    $_dsn = $this->getDriver().':'.$this->getFilename();

    # Set it
    $this->setDsn($_dsn);

    return $_dsn;
  }

  /**
   * Get the Filename
   *
   * @return     string
   */
  public function getFilename(): string
  {
    return $this->filename;
  }

  /**
   * Set filename
   *
   * @param      string  $filename
   *
   * @return     self
   */
  public function setFilename(string $filename)
  {
    $this->filename = $filename;

    return $this;
  }

}
