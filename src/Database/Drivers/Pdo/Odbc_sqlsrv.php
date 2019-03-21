<?php declare(strict_types=1);

/**
 * PDO ODBC Microsoft SQL Server Connection driver
 *
 * Download the ODBC Sql Server 17 driver from the link provided to
 * enable ODBC connections to SQL Server Express.
 *
 * @link      https://www.microsoft.com/en-us/download/details.aspx?id=36434  (version 11)
 * @link      https://www.microsoft.com/en-us/download/details.aspx?id=53339  (version 13.1)
 * @link      https://www.microsoft.com/en-us/download/details.aspx?id=56567  (version 17)
 *
 * @link      https://www.microsoft.com/en-us/download/details.aspx?id=42299  (Microsoft® SQL Server® 2014 Express)
 *
 * @package   Spin
 */

namespace Spin\Database\Drivers\Pdo;

use Spin\Database\PdoConnection;

class Odbc_sqlsrv extends PdoConnection
{
  /**
   * Constructor
   *
   * @param      string  $connectionName  [description]
   * @param      array   $params          [description]
   */
  public function __construct(string $connectionName, array $params=[])
  {
    # Set Driver name
    $this->setDriver('odbc_sqlsrv');

    # PDO options
    if (\count($params['options'] ?? [])==0) {
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
   * Get DSN - ODBC Sql Server formatting
   *
   * @return     string  [description]
   */
  public function getDsn(): string
  {
    // Use any of these or check exact MSSQL ODBC drivername in "ODBC Data Source Administrator"
    //
    // $mssqldriver = 'SQL Server';
    // $mssqldriver = 'SQL Server Native Client 11.0';
    // $mssqldriver = 'ODBC Driver 11 for SQL Server';
    // $mssqldriver = 'ODBC Driver 13 for SQL Server';
    // $mssqldriver = 'ODBC Driver 17 for SQL Server';

    # Build the DSN
    $_dsn = 'odbc:'.
            'Driver=ODBC Driver 11 for SQL Server;'.
            'Server=' . $this->getHost() . '\\SQLEXPRESS' . ($this->getPort()!=0 ? ','.$this->getPort() : '' ) . ';'.
            'Database=' . $this->getSchema();

    # Set it
    $this->setDsn($_dsn);

    return $_dsn;
  }

}
