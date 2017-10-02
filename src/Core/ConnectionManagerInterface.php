<?php

namespace Spin\Core;

use \Spin\Core\Database\PdoConnection;
use \Spin\Core\Database\PdoConnectionInterface;

interface ConnectionManagerInterface
{
  /**
   * Find a connection based on name
   *
   * @param  string   $connectionName Name of the connection (from Config)
   * @return null | object
   */
  function findConnection(string $connectionName);

  /**
   * Get or Create a connection
   *
   * @param  string $connectionName Name of the connection (from Config)
   * @return null | object
   */
  function getConnection(string $connectionName);

  /**
   * Adds the Connection to the Pool
   *
   * @param [type] $connection [description]
   * @return  connection
   */
  function addConnection(PdoConnectionInterface $connection);

  /**
   * Remove a connection from the pool
   *
   * @param  [type] $connection [description]
   * @return bool
   */
  function removeConnection(PdoConnectionInterface $connection);

  /**
   * Get array of containers
   *
   * @return array
   */
  function getConnections(): array;
}
