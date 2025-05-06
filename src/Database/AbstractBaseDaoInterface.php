<?php declare(strict_types=1);

/**
 * AbstractBaseDao
 *
 * @package DaoGen
 */

namespace App\Models;

use \Spin\Database\PdoConnection;
use \Spin\Database\PdoConnectionInterface;
use \App\Models\AbstractBaseEntity;

/**
 * AbstraceBaseDao Interface
 */
interface AbstractBaseDaoInterface
{
  function makeEntity(array $fields=[]): AbstractBaseEntity;
  function fetchCustom(string $sql,array $params=[]);
  function fetchBy(string $field, $value);
  function fetchAllBy(string $field, $value);
  function execCustom(string $sql, array $params=[]): bool;
  function execCustomRowCount(string $sql, array $params=[]): int;
  function execCustomGetLastId(string $sql, array $params=[]): int;
  function fetchCount(string $field,array $params=[]): int;

  function insert(AbstractBaseEntity &$item): bool;
  function update(AbstractBaseEntity $item): bool;
  function delete(AbstractBaseEntity &$item): bool;

  function getConnection(string $connectionName=''): ?PdoConnection;
  function setConnection(?PdoConnection $connection);

  function beginTransaction();
  function commit();
  function rollback();

  function rawQuery(string $sql, array $params=[]);
  function rawExec(string $sql, array $params=[]);

  function getTable(): string;
  function setTable(string $table);
  function getCacheTTL(): int;
  function setCacheTTL(int $cacheTTL=-1);

  # Protected Cache methods
  // protected function cacheSetItem(AbstractBaseEntity $item, $ttl=null )
  // protected function cacheGetItemByField(string $field, string $value)
  // protected function cacheGetById(string $id)
  // protected function cacheGetByCode(string $code)
  // protected function cacheGetByUuid(string $uuid)
  // protected function cacheSetAll(array $items, $ttl=null)
  // protected function cacheClearAll()
  // protected function cacheGetAll()
  // protected function cacheDelete(AbstractBaseEntity $item)
}