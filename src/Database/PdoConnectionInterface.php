<?php declare(strict_types=1);

/**
 * PDO Connection Interface
 *
 * @package   Spin
 */

namespace Spin\Database;

interface PdoConnectionInterface
{
  # PDO Class Interface (must be commented out because of declaration errors)
  // function __construct ( string $dsn, string $username='', string $password='', array $options=[] );
  // function beginTransaction(): bool;
  // function commit(): bool;
  // function errorCode();
  // function errorInfo(): array;
  // function exec(string $statement): int;
  // function getAttribute(int $attribute);
  // function getAvailableDrivers(): array;
  // function inTransaction(): bool;
  // function lastInsertId (string $name=NULL): string;
  // function prepare(string $statement, array $driver_options = array()): \PDOStatement;
  // function query(string $statement): \PDOStatement;
  // function quote(string $string, int $parameter_type=\PDO::PARAM_STR): string;
  // function rollBack(): bool;
  // function setAttribute(int $attribute, $value): bool;

  # Custom additions

  /**
   * Disconnect from database
   *
   * This does not work in PDO since there is no disconnect() feature in PDO
   *
   * @return     bool
   */
  public function disconnect(): bool;

  # Getters

  /**
   * Get DSN - Return the default formatted DSN
   *
   * This method needs to be overridden in DB specific driver
   *
   * @return     string
   */
  public function getDsn(): string;

  /**
   * Get the Name
   *
   * @return     string
   */
  public function getName(): string;

  /**
   * Get the Type
   *
   * @return     string
   */
  public function getType(): string;

  /**
   * Get the Driver
   *
   * @return     string
   */
  public function getDriver(): string;

  /**
   * Get the Schema
   *
   * @return     string
   */
  public function getSchema(): string;

  /**
   * Get the Host
   *
   * @return     string
   */
  public function getHost(): string;

  /**
   * Get the Port
   *
   * @return     int
   */
  public function getPort(): int;

  /**
   * Get Username
   *
   * @return     string
   */
  public function getUsername(): string;

  /**
   * Get Password
   *
   * @return     string
   */
  public function getPassword(): string;

  /**
   * Get Charset
   *
   * @return     string
   */
  public function getCharset(): string;

  /**
   * Get Options
   *
   * @return     array
   */
  public function getOptions(): array;

  /**
   * Get Server Version
   *
   * @return     string
   */
  public function getServerVersion(): string;

  /**
   * Get Client Version
   *
   * @return     string
   */
  public function getClientVersion(): string;

  # Setters

  /**
   * Set DSN connection string
   *
   * @param      string $dsn
   *
   * @return     self
   */
  public function setDsn(string $dsn): PdoConnectionInterface;

  /**
   * Set Connection Name
   *
   * @param      string  $name
   *
   * @return     self
   */
  public function setName(string $name): PdoConnectionInterface;

  /**
   * Set Type
   *
   * @param      string  $type
   *
   * @return     self
   */
  public function setType(string $type): PdoConnectionInterface;

  /**
   * Set Connection host
   *
   * @param      string  $host
   *
   * @return     self
   */
  public function setHost(string $host): PdoConnectionInterface;

  /**
   * Set port
   *
   * @param      int    $port
   *
   * @return     self
   */
  public function setPort(int $port): PdoConnectionInterface;

  /**
   * Set Driver
   *
   * @param      string  $driver
   *
   * @return     self
   */
  public function setDriver(string $driver): PdoConnectionInterface;

  /**
   * Set Schema
   *
   * @param      string  $schema
   *
   * @return     self
   */
  public function setSchema(string $schema): PdoConnectionInterface;

  /**
   * Set Username
   *
   * @param      string  $username  [description]
   *
   * @return     self
   */
  public function setUsername(string $username): PdoConnectionInterface;

  /**
   * Set Password
   *
   * @param      string  $password  [description]
   *
   * @return     self
   */
  public function setPassword(string $password): PdoConnectionInterface;

  /**
   * Set Charset
   *
   * @param      string  $charset  Charset to use
   *
   * @return     self
   */
  public function setCharset(string $charset): PdoConnectionInterface;

  /**
   * Set Connection Options
   *
   * @param      array  $options
   *
   * @return     self
   */
  public function setOptions(array $options): PdoConnectionInterface;

  /**
   * Set ServerVersion
   *
   * @param      string  $serverVersion
   *
   * @return     self
   */
  public function setServerVersion(string $serverVersion): PdoConnectionInterface;

  /**
   * Set clientVersion
   *
   * @param      string  $clientVersion  The client version
   *
   * @return     self
   */
  public function setClientVersion(string $clientVersion): PdoConnectionInterface;

  /**
   * Execute a SELECT statement
   *
   * @param   string  $sql                SQL statement to execute (SELECT ...)
   * @param   array   $params             Array with Bind params
   * @param   bool    $autoTransactions   Optional. TRUE enables automatic transaction handling
   *
   * @return  array                       Array with fetched rows
   *
   * @throws  \Exception
   */
  public function rawQuery(string $sql, array $params = [], bool $autoTransactions = true): array;

  /**
   * Execute an INSERT, UPDATE or DELETE statement
   *
   * @param   string  $sql                SQL statement to execute (INSERT, UPDATE, DELETE ...)
   * @param   array   $params             Array with Bind params
   * @param   bool    $autoTransactions   Optional. TRUE enables automatic transaction handling
   *
   * @return  null|int                    `null` or number of rows affected
   *
   * @throws  \Exception
   */
  public function rawExec(string $sql, array $params = [], bool $autoTransactions = true): ?int;
}
