<?php declare(strict_types=1);

namespace Spin\Database;

interface PdoConnectionInterface
{
  # PDO Class Interface
  // function __construct ( string $dsn [, string $username [, string $password [, array $options ]]] )
  // function beginTransaction(): bool;
  // function commit(): bool;
  // function errorCode(): mixed;
  // function errorInfo(): array;
  // function exec(string $statement): int;
  // function getAttribute(int $attribute): mixed;
  // function getAvailableDrivers(): array;
  // function inTransaction(): bool;
  // function lastInsertId (string $name=NULL): string;
  // function prepare(string $statement, array $driver_options = array()): PDOStatement;
  // function query(string $statement): PDOStatement;
  // function quote(string $string, int $parameter_type=PDO::PARAM_STR): string;
  // function rollBack(): bool;
  // function setAttribute(int $attribute , mixed $value): bool;

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
  function setPort($port);
  function setDriver(string $driver);
  function setSchema(string $schema);
  function setUsername(string $username);
  function setPassword(string $password);
  function setCharset(string $charset);
  function setOptions(array $options);

  /**
   * Execute a raw SELECT statement
   *
   * @param  string $sql          SQL statement to execute (SELECT ...)
   * @param  array  $params       Bind params
   * @return array                Array with fetched rows
   */
  public function rawQuery(string $sql, array $params=[]);

  /**
   * Execute an raw INSERT, UPDATE or DELETE statement
   *
   * @param  string $sql          SQL statement to execute (INSERT, UPDATE, DELETE ...)
   * @param  array  $params       Bind params
   * @return bool                 True if rows affected > 0
   */
  public function rawExec(string $sql, array $params=[]);
}
