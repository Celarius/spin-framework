<?php declare(strict_types=1);

namespace Spin\Core;

use \Spin\Database\PdoConnection;
use \Spin\Database\PdoConnectionInterface;

/**
 * Contract for connection managers that resolve, create, and manage database
 * connections, exposing a pool and lifecycle operations.
 */
interface ConnectionManagerInterface
{
  /**
   * Find a connection based on name
   *
   * @param      string  $name   Name of the connection (from Config)
   *
   * @return     null|PdoConnection
   */
  function findConnection(string $name);

  /**
   * Get or Create a connection
   *
   * @param      null|string  $name   Name of the connection (from Config)
   *
   * @return     null|PdoConnection
   */
  function getConnection(?string $name=null);

  /**
   * Adds the Connection to the Pool
   *
   * @param      [type]  $connection  [description]
   *
   * @return     PdoConnection
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
