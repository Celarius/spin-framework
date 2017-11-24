<?php declare(strict_types=1);

/**
 * PDO Firebird Connection driver
 *
 * https://www.firebirdsql.org
*/

namespace Spin\Database\Drivers\Pdo;

use Spin\Database\PdoConnection;

class Firebird extends PdoConnection
{
  /**
   * Constructor
   *
   * @param string $connectionName [description]
   * @param array  $params         [description]
   */
  public function __construct(string $connectionName, array $params=[])
  {
    # Set Driver name
    $this->setDriver('firebird');

    # PDO options
    if (count($params['options'] ?? [])==0) {
      $params['options'] = [
          \PDO::ATTR_PERSISTENT => TRUE,
          \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
          \PDO::ATTR_AUTOCOMMIT => FALSE
        ];
    }

    # Parent Constructor
    parent::__construct($connectionName,$params);
  }

  /**
   * Get DSN - Firebird formatting
   *
   * @return string       [description]
   */
  public function getDsn(): string
  {
/*
Firebird:
  $connection = new \PDO('firebird:host=<host>:<port>;dbname=<schema>;charset=<charset>', $user, $pass,
    array(
      \PDO::ATTR_PERSISTENT => TRUE
      \PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      \PDO::ATTR_AUTOCOMMIT => FALSE
    )
  );
 */
    # Build the DSN
    $_dsn = $this->getDriver().':'.
            'host='.$this->getHost().($this->getPort()!=0 ? ':'.$this->getPort() : '' ).';'.
            'dbname='.$this->getSchema().';'.
            'charset='.$this->getCharset();

    # Set it
    $this->setDsn($_dsn);

    return $_dsn;
  }

}
