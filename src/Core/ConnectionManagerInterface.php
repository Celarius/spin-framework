<?php declare(strict_types=1);

namespace Spin\Core;

use \Spin\Database\PdoConnection;
use \Spin\Database\PdoConnectionInterface;

interface ConnectionManagerInterface
{
  /**
   * Find a connection based on name
   *
   * @param      string  $name   Name of the connection (from Config)
   * 
   * @return     null  | object
   */
  function findConnection(string $name);

  /**
   * Get or Create a connection
   *
   * @param      string  $name   Name of the connection (from Config)
   *
   * @return     null  | object
   */
  function getConnection(string $name=null);

  /**
   * Adds the Connection to the Pool
   *
   * @param      [type]  $connection  [description]
   *
   * @return     connection
   */
  function addConnection(PdoConnectionInterface $connection);

  /**
   * Remove a connection from the pool
   *
   * @param      string  $name   Name of connection to remove
   *
   * @return     bool
   */
  function removeConnection(string $name);

  /**
   * Get array of containers
   *
   * @return     array
   */
  function getConnections(): array;
}
