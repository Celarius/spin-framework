<?php declare(strict_types=1);

/**
 * PDO CockroachDb Connection driver
 *
 * https://www.cockroachlabs.com
 * 
 * @package    Spin
*/

namespace Spin\Database\Drivers\Pdo;

use Spin\Database\PdoConnection;

class CockroachDb extends PdoConnection
{
  /** @var      string        SSL Mode: [disable|allow|prefer|require|verify-ca|verify-full] */
  protected $sslmode = 'prefer';

  /**
   * Constructor
   *
   * @param      string  $connectionName  [description]
   * @param      array   $params          [description]
   */
  public function __construct(string $connectionName, array $params=[])
  {
    # Set Driver name
    $this->setDriver('cockroachdb'); // lowercase to follow rest of PDO drivers standard

    # Set SSL Mode
    $this->setSSLMode($params['sslmode'] ?? 'disable');

    # PDO options
    if (count($params['options'] ?? [])==0) {
      $params['options'] = [
          \PDO::ATTR_PERSISTENT => TRUE,
          \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
          \PDO::ATTR_EMULATE_PREPARES => TRUE
        ];
    }

    # Parent Constructor
    parent::__construct($connectionName,$params);
  }

  /**
   * Get DSN - CockraochDb formatting
   *
   * @return string       [description]
   */
  public function getDsn(): string
  {
/*
  $connection = new PDO('pgsql:host=<hostname>;port=26257;dbname=<schema>;sslmode=<mode>', $user, $pass,
    array(
      PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_EMULATE_PREPARES => true,
  ));
*/
    # Build the DSN
    $_dsn = 'pgsql:' . // Cockroach uses PostgreSql as driver
            'host=' . $this->getHost().';' .
            'port=' . ($this->getPort()!=0 ? $this->getPort() . ':' : '26257' ) .
            'dbname=' . $this->getSchema().';' .
            'sslmode=' . $this->getSSLMode();

    # Set it
    $this->setDsn($_dsn);

    return $_dsn;
  }

  /**
   * Get the sslmode
   *
   * @return string
   */
  public function getSSLMode(): string
  {
    return $this->sslmode;
  }

  /**
   * Set sslmode
   *
   * @param   string $sslmode
   * @return  self
   */
  public function setSSLMode(string $sslmode)
  {
    $this->sslmode = $sslmode;

    return $this;
  }

}
