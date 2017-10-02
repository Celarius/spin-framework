<?php

namespace Spin\Core\Database;

use \Spin\Core\Database\AbstractBaseDaoInterface;

abstract class AbstractBaseDao implements AbstractBaseDaoInterface
{
  protected $connectionName;
  protected $connection;

  /**
   * Constructor
   *
   * @param \Nofuzz\Database\PdoConnectionInterface $connection
   */
  public function __construct(string $connectionName='')
  {
    $this->connectionName = $connectionName;
    $this->connection = null;
  }

  /**
   * Get the DB connection assinged to this DAO object
   *
   * @return null|\Nofuzz\Database\PdoConnectionInterface
   */
  public function getConnection()
  {
    $this->connection = db($this->connectionName);

    return $this->connection;
  }

  /**
   * Get the DB connection assinged to this DAO object
   *
   * @return null|\Nofuzz\Database\PdoConnectionInterface
   * @deprecated 0.5.6 Removed in version 0.5.6
   */
  public function db()
  {
    return $this->getConnection();
  }

  /**
   * Begin transaction if not already started
   *
   * @return bool
   */
  public function beginTransaction()
  {
    if (!$this->getConnection()->inTransaction()) {
      return $this->getConnection()->beginTransaction();
    }
    return false;
  }

  /**
   * Commit active transaction
   *
   * @return bool
   */
  public function commit()
  {
    if ($this->getConnection()->inTransaction()) {
      return $this->getConnection()->commit();
    }
    return false;
  }

  /**
   * Rollback active transaction
   *
   * @return bool
   */
  public function rollback()
  {
    if ($this->getConnection()->inTransaction()) {
      return $this->getConnection()->rollback();
    }
    return false;
  }

  /**
   * Execute a SELECT statement
   *
   * @param  string $sql          SQL statement to execute (SELECT ...)
   * @param  array  $params       Bind params
   * @return array                Array with fetched rows
   */
  public function rawQuery(string $sql, array $params=[])
  {
    return $this->getConnection()->rawQuery($sql, $params);
  }

  /**
   * Execute an INSERT, UPDATE or DELETE statement
   *
   * @param  string $sql          SQL statement to execute (INSERT, UPDATE, DELETE ...)
   * @param  array  $params       Bind params
   * @return bool                 True if rows affected > 0
   */
  public function rawExec(string $sql, array $params=[])
  {
    return $this->getConnection()->rawExec($sql, $params);
  }

}
