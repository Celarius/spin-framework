<?php declare(strict_types=1);

namespace Spin\Database;

interface AbstractBaseDaoInterface
{
  /**
   * Constructor
   *
   * @param      string  $connectionName
   */
  function __construct(string $connectionName);

  /**
   * Get the DB connection assinged to this DAO object
   *
   * @return     null|PdoConnection
   */
  function getConnection();

  /**
   * Get the DB connection assinged to this DAO object
   *
   * @return     null|PdoConnection
   * @deprecated 0.5.6 Removed in version 0.5.6
   */
  function db();

  /**
   * Begin transaction if not already started
   *
   * @return     bool
   */
  function beginTransaction();

  /**
   * Commit active transaction
   *
   * @return     bool
   */
  function commit();

  /**
   * Rollback active transaction
   *
   * @return     bool
   */
  function rollback();

  /**
   * Execute a SELECT statement
   *
   * @param      string  $sql     SQL statement to execute (SELECT ...)
   * @param      array   $params  Bind params
   * 
   * @return     array  Array with fetched rows
   */
  function rawQuery(string $sql, array $params=[]);

  /**
   * Execute an INSERT, UPDATE or DELETE statement
   *
   * @param      string  $sql     SQL statement to execute (INSERT, UPDATE,
   *                              DELETE ...)
   * @param      array   $params  Bind params
   * 
   * @return     bool  True if rows affected > 0
   */
  function rawExec(string $sql, array $params=[]);
}
