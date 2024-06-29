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
  function connect(): bool;
  function disconnect(): bool;

  # Getters
  function connected(): bool;
  function getDsn(): string;
  function getName(): string;
  function getType(): string;
  function getDriver(): string;
  function getSchema(): string;
  function getHost(): string;
  function getPort(): int;
  function getUsername(): string;
  function getPassword(): string;
  function getCharset(): string;
  function getOptions(): array;
  function getServerVersion(): string;
  function getClientVersion(): string;

  # Setters
  function setDsn(string $dsn);
  function setName(string $name);
  function setType(string $type);
  function setHost(string $host);
  function setPort(int $port);
  function setDriver(string $driver);
  function setSchema(string $schema);
  function setUsername(string $username);
  function setPassword(string $password);
  function setCharset(string $charset);
  function setOptions(array $options);
  function setServerVersion(string $serverVersion);
  function setClientVersion(string $clientVersion);

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
  public function rawQuery(string $sql, array $params=[], bool $autoTransactions=true): array;

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
  public function rawExec(string $sql, array $params = [], bool $autoTransactions = true);
}
