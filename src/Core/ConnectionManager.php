<?php declare(strict_types=1);

/**
 * ConnectionManager
 *
 * Manages all Database Connections
 *
 * When we 1st need a DB connection, it is created.
 */

/*
Example:

  # Get a Connection by Name
  $dbCon = db('name1'); // Gives you back a PDO Object connected to the Database

  # Get 1st available Connection
  $dbCon = db('');
*/

namespace Spin\Core;

use \Spin\Core\AbstractBaseClass;
use \Spin\Core\ConnectionManager;
use \Spin\Core\ConnectionManagerInterface;
use \Spin\Core\Database\PdoConnection;
use \Spin\Core\Database\PdoConnectionInterface;


class ConnectionManager extends AbstractBaseClass implements ConnectionManagerInterface
{
  protected $connections = [];

  /**
   * Find a connection based on name
   *
   * @param  string   $connectionName       Name of the connection (from Config)
   * @return null | object
   */
  public function findConnection(string $connectionName)
  {
    if ( empty($connectionName) ) {
      # Take first available connection
      $connection = reset($this->connections);
      if ($connection === false)
        return null;
    } else {
      # Attempt to find the connection from the pool
      $connection = ( $this->connections[strtolower($connectionName)] ?? null);
    }

    return $connection;
  }

  /**
   * Get or Create a connection
   *
   * @param  string $connectionName         Name of the connection (from Config)
   * @return null | object
   */
  public function getConnection(string $connectionName)
  {
    $connection = $this->findConnection($connectionName);

    if (is_null($connection)) {
      $connection = $this->createConnection($connectionName);

      if (!is_null($connection)) {
        $this->addConnection($connection);
      }
    }

    return $connection;
  }

  /**
   * Adds the Connection to the Pool
   *
   * @param [type] $connection [description]
   * @return  connection
   */
  public function addConnection(PdoConnectionInterface $connection)
  {
    $this->connections[strtolower($connection->getName())] = $connection;

    return $connection;
  }

  /**
   * Remove a connection from the pool
   *
   * @param  [type] $connection [description]
   * @return bool
   */
  public function removeConnection(PdoConnectionInterface $connection)
  {
    $connection = $this->findConnection($connectionName);

    if ($connection) {
      $connection->disconnect();
      unset( $this->connections[strtolower($connection->getName())] );
      unset($connection);
      $connection = null;
    }

    return is_null($connection);
  }

  /**
   * Creates a Connection based on the $connectionName
   *
   * Finds the corresponding connection in the config and uses it
   * to instanciate a connection. If the ConnectionName is empty, we will use
   * the 1st available record in the Connections list.
   *
   * @param  string $connectionName [description]
   * @return [type]                 [description]
   */
  protected function createConnection(string $connectionName)
  {
    # Get the connection form the connections array
    $connection = $this->connections[strtolower($connectionName)] ?? null;

    # If no connection found, and the list is empty, read in 1st one
    if (is_null($connection) && count($this->connections)==0 ) {
      # Get connections configuration
      $arr = config()->get('connections');
      reset($arr);
      $connectionName = key($arr);
    }

    if (is_null($connection)) {
      # Get connection configuration
      $connConf = config()->get('connections.'.$connectionName);

      # Type="PDO"
      if ( strcasecmp($connConf['type'] ?? '','PDO')==0 ) {
        $className = '\\Nofuzz\\Database\\Drivers\\'.ucfirst($connConf['type']).'\\'.ucfirst($connConf['driver']) ;

        try {
          # Create the Connection (PdoConnection)
          $connection = new $className($connectionName, $connConf);
        } catch (\Exception $e) {
          logger()->critical(
            $e->getMessage(),
            ['trace'=>$e->getTraceAsString()]
          );
        }
      }
    }

    return $connection;
  }

  /**
   * Get array of containers
   *
   * @return array
   */
  public function getConnections(): array
  {
    return $this->connections;
  }

}
