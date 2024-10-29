<?php declare(strict_types=1);

/**
 * AbstractBaseDao
 *
 * @package DaoGen
 */

namespace App\Models;

use \Spin\Database\PdoConnection;
use \Spin\Database\PdoConnectionInterface;
use \App\Models\AbstractBaseDaoInterface;
use \App\Models\AbstractBaseEntity;

/**
 * AbstraceBaseDao Class
 */
abstract class AbstractBaseDao implements AbstractBaseDaoInterface
{
  /**
   * The connection name
   * @var string
   */
  protected $connectionName;

  /**
   * The connection parameters (optional)
   * @var array
   */
  protected $params;

  /**
   * The connection
   * @var PdoConnection
   */
  protected $connection;

  /**
   * The table name
   * @var string
   */
  protected $table;

  /**
   * The cache TTL for entity Items
   * @var int
   */
  protected $cacheTTL;

# *******************************************************************************************************************

  /**
   * Constructor
   *
   * @param      string  $connectionName  Database ConnectionName
   * @param      int     $cacheTTL        Seconds to Cache the entries.
   *                                      0=Forever, -1=Do not cache
   * @param      array   $params          The connection parameters (optional)
   */
  public function __construct(string $connectionName='', int $cacheTTL=-1, array $params=[])
  {
    $this->connectionName = $connectionName;
    $this->params = $params;

    $this->setConnection(null);
    $this->setTable('');
    $this->setCacheTTL($cacheTTL);
  }

  /**
   * Fetch all rows based on $sql and $prams
   *
   * @param   string $sql                                       SQL to execute
   * @param   array<mixed> $params                              Array with paramters key=value
   *
   * @return  array<AbstractBaseEntity>                         Array with result
   */
  public function fetchCustom(string $sql,array $params=[])
  {
    # If we have no connection ..
    if (is_null($this->getConnection())) return [];

    # Replace {table} with the table-name
    $sql = \str_replace('{table}', $this->getTable(), $sql);

    # Default to no rows returned
    $rows = [];

    $autoCommit = $this->beginTransaction();

    try {

      # Prepare
      if ($sth=$this->getConnection()->prepare($sql)) {
        # Binds
        $this->setBinds($sth,$params);

        if ($sth->execute()) {
          # Fetch everyting in one go
          $result = $sth->fetchAll(\PDO::FETCH_ASSOC);

          # Loop result, converting each entry with makeEntity()
          foreach ($result as $i=>$row) {
            $rows[] = $this->makeEntity($row);
            unset($result[$i]);
          }
          unset($result);
        }
        $sth->closeCursor();
      }
      if ($autoCommit) $this->commit();

    } catch (\Exception $e) {
      # Rollback the current transaction
      if ($autoCommit) $this->rollback();

      # Log the exception
      \logger()->critical($e->getMessage(),[
        'sql'     => $sql,
        'params'  => $params,
        'rid'     => \container('requestId'),
        'trace'   => $e->getTraceAsString()]
      );

      # Rethrow the exception so calling function can handle it
      throw $e;
    }

    return $rows;
  }

  /**
   * Fetch a record by field
   *
   * @param      string  $field  Field to match agains
   * @param      mixed   $value  Value to match with
   *
   * @return     array|null
   */
  public function fetchBy(string $field, $value)
  {
    # If we have no connection ..
    if (is_null($this->getConnection())) return null;

    if ($item = $this->cacheGetItemByField($field,$value)) return $item;

    $item =
      $this->fetchCustom(
        'SELECT * FROM {table} WHERE '.$field.' = :'.\strtoupper($field),
        [':'.\strtoupper($field) => $value]
      )[0] ?? null;

    if ($item) $this->cacheSetItem($item);

    return $item;
  }

  /**
   * Fetch all records by a $field matching $value
   *
   * @param      string  $field  Field to match agains
   * @param      mixed   $value  Value to match with
   *
   * @return     array   Array of AbstractBaseEntity objects
   */
  public function fetchAllBy(string $field, $value)
  {
    $items =
      $this->fetchCustom(
        'SELECT * FROM {table} WHERE '.$field.' = :'.\strtoupper($field),
        [':'.\strtoupper($field) => $value]
      ) ?? [];

    return $items;
  }

  /**
   * Execute $sql with $params
   *
   * @param      string  $sql     [description]
   * @param      array   $params  [description]
   *
   * @return     bool
   */
  public function execCustom(string $sql, array $params=[]): bool
  {
    # Replace {table} with the table-name
    $sql = \str_replace('{table}', $this->getTable(), $sql);

    # Default result
    $result = false;

    $_retry = 0;
    $_retryWait = 250; // milliseconds
    $_maxRetries = 5;

    try {
      $autoCommit = $this->beginTransaction();
      if ($sth = $this->getConnection()->prepare($sql)) {
        # Binds
        $this->setBinds($sth,$params);

        # Retry for "DEADLOCK" exceptions
        while (0==0) {
          try {
            # Execute query
            $result = $sth->execute();
            break;

          } catch (\Exception $e) {
            # Do we have a DeadLock ??
            if ( \stripos($e->getMessage(),'deadlock')!==false ) {
              \usleep(($_retryWait+($_retry*$_retryWait)) * 1000); // Wait a little before trying again
              $_retry++; // Increment retry counter
              if ($_retry > $_maxRetries) break;
            } else {
              throw $e; // rethrow the error, was something else than a deadlock
            }
          }
        } // while

      }
      if ($autoCommit) $this->commit();

    } catch (\Exception $e) {
      # Rollback the current transaction
      if ($autoCommit) $this->rollback();

      # Log the exception
      \logger()->critical($e->getMessage(),['sql'=>$sql,'params'=>$params,'trace'=>$e->getTraceAsString()]);

      # Rethrow the exception so calling function can handle it
      throw $e;
    }

    return $result;
  }

  /**
   * Execute $sql with $params and return number of rows affected
   *
   * @param   string  $sql     [description]
   * @param   array   $params  [description]
   *
   * @return  int     Number of rows affected. -1 = failure
   */
  public function execCustomRowCount(string $sql, array $params=[]): int
  {
    # Replace {table} with the table-name
    $sql = \str_replace('{table}', $this->getTable(), $sql);

    # Default result
    $result = -1; // fail

    $_retry = 0;
    $_retryWait = 250; // milliseconds
    $_maxRetries = 5;

    try {

      $autoCommit = $this->beginTransaction();
      if ($sth = $this->getConnection()->prepare($sql))
      {
        # Binds
        $this->setBinds($sth,$params);

        # Retry for "DEADLOCK" exceptions
        while (0==0) {
          try {
            # Execute query
            if ($sth->execute()) {
              $result = $sth->rowCount();
            }
            break;

          } catch (\Exception $e) {
            # Do we have a DeadLock ??
            if ( \stripos($e->getMessage(),'deadlock')!==false ) {
              \usleep(($_retryWait+($_retry*$_retryWait)) * 1000); // Wait a little before trying again
              $_retry++; // Increment retry counter
              if ($_retry > $_maxRetries) break;
            } else {
              throw $e; // rethrow the error, was something else than a deadlock
            }
          }
        } // while

      }
      if ($autoCommit) $this->commit();

    } catch (\Exception $e) {
      # Rollback the current transaction
      if ($autoCommit) $this->rollback();

      # Log the exception
      \logger()->critical($e->getMessage(),['sql'=>$sql,'params'=>$params,'trace'=>$e->getTraceAsString()]);

      # Rethrow the exception so calling function can handle it
      throw $e;
    }

    return $result;
  }

  /**
   * Execute $sql with $params
   *
   * @param      string  $sql     [description]
   * @param      array   $params  [description]
   *
   * @return     int     $this->getConnection()->lastInsertId()
   */
  public function execCustomGetLastId(string $sql, array $params=[]): int
  {
    # Replace {table} with the table-name
    $sql = \str_replace('{table}', $this->getTable(), $sql);

    # Defaults
    $result = 0;

    $_retry = 0;
    $_retryWait = 250; // milliseconds
    $_maxRetries = 5;

    try {
      $autoCommit = $this->beginTransaction();

      if ( \strcasecmp($this->getConnection()->getDriver(),'mysql')!=0 ) {
        # Firebird, PostGreSQL and Oracle support RETURNING clauses
        $sql .= ' RETURNING id';
      } else {
        # MySql driver, not supporting RETURNING statements
      }

      if ($sth = $this->getConnection()->prepare($sql))
      {
        # Binds
        $this->setBinds($sth,$params);

        # Retry for "DEADLOCK" exceptions
        while (0==0) {
          try {
            # Execute query
            if ($sth->execute()) {
              if ( \strcasecmp($this->getConnection()->getDriver(),'mysql')!=0 ) {
                # Firebird, PostGreSQL, Oracle, CockroachDb support RETURNING clauses
                $row = $sth->fetch(\PDO::FETCH_ASSOC);
                $result = \array_change_key_case($row,\CASE_LOWER)['id'] ?? 0;
              } else {
                # MySql driver, not supporting RETURNING statements, instead uses LastInsertId()
                $result = $this->getConnection()->lastInsertId();
              }
            }
            break;

          } catch (\Exception $e) {
            # Do we have a DeadLock ??
            if ( \stripos($e->getMessage(),'deadlock')!==false ) {
              \usleep(($_retryWait+($_retry*$_retryWait)) * 1000); // Wait a little before trying again
              $_retry++; // Increment retry counter
              if ($_retry > $_maxRetries) break;
            } else {
              throw $e; // rethrow the error, was something else than a deadlock
            }
          }
        } // while

      }
      if ($autoCommit) $this->commit();

    } catch (\Exception $e) {
      # Rollback the current transaction
      if ($autoCommit) $this->rollback();

      # Log the exception
      \logger()->critical($e->getMessage(),['sql'=>$sql,'params'=>$params,'trace'=>$e->getTraceAsString()]);

      # Rethrow the exception so calling function can handle it
      throw $e;
    }

    return (int) $result;
  }

  /**
   * Fetch Count based on params
   *
   * @param      string  $field   Field to count
   * @param      array   $params  [description]
   *
   * @return     int
   */
  public function fetchCount(string $field,array $params=[]): int
  {
    # If we have no connection ..
    if (is_null($this->getConnection())) return 0;

    $sql = 'SELECT COUNT('.$field.') AS cnt FROM '.$this->getTable();

    if (\count($params)>0) {
      $sql .=' WHERE ';
      foreach ($params as $param => $value) {
        $sql .= $param.' = :'.\strtoupper($param).' AND ';
      }
      $sql = \substr($sql,0,-5); // rtrim has a bug with ' AND ' at the end!? Confirmed in PHP v7.1.1
    }

    # Default to no rows returned
    $rows = [];
    // try {
      $autoCommit = $this->beginTransaction();
      # Prepare
      if ($sth=$this->getConnection()->prepare($sql)) {
        # Binds
        foreach ($params as $bind=>$value) {
          $sth->bindValue(\strtoupper(':'.$bind), $value);
        }
        # Exec
        if ($sth->execute()) {
          $rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
        }
        $sth->closeCursor();
      }
      if ($autoCommit) $this->commit();

    // } catch (\Exception $e) {
    //   logger()->critical($e->getMessage(),['rid'=>app('requestId'),'sql'=>$sql,'params'=>$params,'trace'=>$e->getTraceAsString()]);
    //   if ($autoCommit) $this->rollback();
    // }

    return (int) ($rows[0]['cnt']) ?? 0;
  }

  /**
   * Delete
   *
   * @param      AbstractBaseEntity  $item
   *
   * @return     bool
   */
  public function delete(AbstractBaseEntity &$item): bool
  {
    $ok =
      $this->execCustom(
        'DELETE FROM {table} WHERE id = :ID ',
        [':ID' => $item->getId()]
      );

    if ($ok) {
      $this->cacheDelete($item);
      $item->setId(0);

      # Clear the fetchAll() cache
      $this->cacheClearAll();
    }

    return $ok;
  }


  /**
   * Cache one $item
   *
   * @param      AbstractBaseEntity  $item   Item to Set in cache
   * @param      mixed  $ttl    Optional. Overrides default TTL. Seconds
   *
   * @return     bool
   */
  protected function cacheSetItem(AbstractBaseEntity $item, $ttl=null )
  {
    if (is_null($ttl))
      $ttl = $this->getCacheTTL();

    if ($ttl>=0 && \cache() && $item) {
      # Add Item to caches
      if (\method_exists($item,'getId') && $item->getId()>0) \cache()->set(static::class.':id:'.$item->getId(), $item, $ttl);
      if (\method_exists($item,'getUuid') && !empty($item->getUuid())) \cache()->set(static::class.':uuid:'.$item->getUuid(), $item, $ttl);
      if (\method_exists($item,'getCode') && !empty($item->getCode())) \cache()->set(static::class.':code:'.$item->getCode(), $item, $ttl);
      if (\method_exists($item,'getEmail') && !empty($item->getEmail())) \cache()->set(static::class.':email:'.$item->getEmail(), $item, $ttl);
    }

    return true;
  }

  /**
   * Get cached $item, based on field name
   *
   * @param      string  $field  field to search in
   * @param      string  $value  value to search for
   *
   * @return     $item   | false
   */
  protected function cacheGetItemByField(string $field, $value)
  {
    $cacheKey = static::class.':'.$field.':'.$value;
    if (\cache() && \cache()->has($cacheKey)) {
      return \cache()->get($cacheKey);
    }

    return false;
  }

  /**
   * Get cached $item by $id
   *
   * @param      string  $id     Item ID to look for
   *
   * @return     $item   | false
   */
  protected function cacheGetById(string $id)
  {
    $cacheKey = static::class.':id:'.$id;
    if (\cache() && \cache()->has($cacheKey)) {
      return \cache()->get($cacheKey);
    }

    return false;
  }

  /**
   * Get cached $item by $code
   *
   * @param      string  $code   Item code to look for
   *
   * @return     $item   | false
   */
  protected function cacheGetByCode(string $code)
  {
    $cacheKey = static::class.':code:'.$code;
    if (\cache() && \cache()->has($cacheKey)) {
      return \cache()->get($cacheKey);
    }

    return false;
  }

  /**
   * Get cached $item by $uuid
   *
   * @param      string  $uuid   Item uuid to look for
   *
   * @return     $item   | false
   */
  protected function cacheGetByUuid(string $uuid)
  {
    $cacheKey = static::class.':uuid:'.$uuid;
    if (\cache() && \cache()->has($cacheKey)) {
      return \cache()->get($cacheKey);
    }

    return false;
  }

  /**
   * Cache array of all $items
   *
   * @param      array  $items  Items array to set in cache
   * @param      mixed  $ttl    Optional. Overrides default TTL. Seconds
   *
   * @return     bool
   */
  protected function cacheSetAll(array $items, $ttl=null)
  {
    if (is_null($ttl))
      $ttl = $this->getCacheTTL();

    if ($ttl>=0 && \cache() && \count($items)>0) {
      \cache()->set(static::class.':all', $items, $ttl);
    }

    return true;
  }

  /**
   * Clear Cache array of all $items
   *
   * @return     bool
   */
  protected function cacheClearAll()
  {
    if (\cache()) {
      return \cache()->delete(static::class.':all');
    }

    return false;
  }

  /**
   * Get all cached items list
   *
   * @return     array|false
   */
  protected function cacheGetAll()
  {
    $cacheKey = static::class.':all';
    if (\cache() && $cacheKey && \cache()->has($cacheKey)) {
      return \cache()->get($cacheKey);
    }

    return false;
  }

  /**
   * Delete an $item from cache
   *
   * @param      AbstractBaseEntity  $item   Item to delete from cache
   *
   * @return     bool
   */
  protected function cacheDelete(AbstractBaseEntity $item)
  {
    if (\cache() && $item) {
      # Remove Individual Item
      if (\method_exists($item,'getId') && $item->getId()>0) \cache()->delete(static::class.':id:'.$item->getId());
      if (\method_exists($item,'getUuid') && !empty($item->getUuid())) \cache()->delete(static::class.':uuid:'.$item->getUuid());
      if (\method_exists($item,'getCode') && !empty($item->getCode())) \cache()->delete(static::class.':code:'.$item->getCode());
      # Clear the ALL cache
      \cache()->delete(static::class.':all');
    }

    return true;
  }

  /**
   * Binds the Parameters to a Statement
   *
   * @param $statement
   * @param array $params
   * @return void
   */
  private function setBinds(\PDOStatement &$statement, array $params): void
  {
    # Binds
    foreach ($params as $bind=>$value)
    {
      if (\is_int($value)) {
        $statement->bindValue( ':'.\ltrim($bind,':'), (int)$value, \PDO::PARAM_INT); // INT !
      } else
      if (\is_bool($value)) {
        $statement->bindValue( ':'.\ltrim($bind,':'), (bool)$value, \PDO::PARAM_BOOL);
      } else
      if (\is_null($value)) {
        $statement->bindValue( ':'.\ltrim($bind,':'), null, \PDO::PARAM_NULL);
      } else {
        $statement->bindValue( ':'.\ltrim($bind,':'), $value, \PDO::PARAM_STR);
      }
    }

    return ;
  }

  /**
   * Wrapper function for PdoConnection.beginTransaction
   *
   * @return     bool
   */
  public function beginTransaction()
  {
    if (!$this->getConnection()->inTransaction()) {
      return $this->getConnection()->beginTransaction();
    }
    return false;
  }

  /**
   * Wrapper function for PdoConnection.commit
   *
   * @return     bool
   */
  public function commit()
  {
    if ($this->getConnection()->inTransaction()) {
      return $this->getConnection()->commit();
    }
    return false;
  }

  /**
   * Wrapper function for PdoConnection.rollback
   *
   * @return     bool
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
   * @param      string  $sql     SQL statement to execute (SELECT ...)
   * @param      array   $params  Bind params
   *
   * @return     array   Array with fetched rows
   */
  public function rawQuery(string $sql, array $params=[])
  {
    return $this->getConnection()->rawQuery($sql, $params);
  }

  /**
   * Execute an INSERT, UPDATE or DELETE statement
   *
   * @param      string  $sql     SQL statement to execute (INSERT, UPDATE,
   *                              DELETE ...)
   * @param      array   $params  Bind params
   *
   * @return     bool    True if rows affected > 0
   */
  public function rawExec(string $sql, array $params=[])
  {
    return $this->getConnection()->rawExec($sql, $params);
  }

  /**
   * Get the DB connection assinged to this DAO object
   *
   * @param      string  $connectionName
   *
   * @return     null|PdoConnection
   */
  public function getConnection(string $connectionName=''): ?PdoConnection
  {
    # Obtain the connection from helper function db()
    return \db( (empty($connectionName) ? $this->connectionName : $connectionName), $this->params );
  }

  /**
   * Set the DB connection
   *
   * @param      PdoConnection  $connection
   *
   * @return     self
   */
  public function setConnection(?PdoConnection $connection)
  {
    $this->connection = $connection;

    return $this;
  }

  /**
   * Get Table
   *
   * @return     string
   */
  public function getTable(): string
  {
    return $this->table;
  }

  /**
   * Set Table
   *
   * @param      string  $table  [description]
   *
   * @return     self
   */
  public function setTable(string $table)
  {
    $this->table = $table;

    return $this;
  }

  /**
   * Get CacheTTL
   *
   * @return     int
   */
  public function getCacheTTL(): int
  {
    return $this->cacheTTL;
  }

  /**
   * Set CacheTTL
   *
   * @param      int   $cacheTTL  TTL seconds
   *
   * @return     self
   */
  public function setCacheTTL(int $cacheTTL=0)
  {
    $this->cacheTTL = $cacheTTL;

    return $this;
  }

}
