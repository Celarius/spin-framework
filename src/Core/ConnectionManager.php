<?php declare(strict_types=1);

/**
 * ConnectionManager
 *
 * Manages all Database Connections
 *
 * When we 1st need a DB connection, it is created via the ConnectionFactory
 */

/*
Example:

  # Get a Connection by Name
  $dbCon = db('name1'); // Gives you back a PdoConnection object

  # Get 1st available Connection in connections list
  $dbCon = db('');
*/

namespace Spin\Core;

use \Spin\Core\AbstractBaseClass;
use \Spin\Core\ConnectionManagerInterface;
use \Spin\Database\PdoConnection;
use \Spin\Database\PdoConnectionInterface;

class ConnectionManager extends AbstractBaseClass implements ConnectionManagerInterface
{
  /** @var array List of Instantiated Connections */
  protected $connections = [];

  /**
   * Get or Create a connection
   *
   * @param  string $connectionName         Name of the connection (from Config)
   * @return null | object
   */
  public function getConnection(string $connectionName)
  {
    # Find the connection (if we already have it created)
    $connection = $this->findConnection($connectionName);

    if (is_null($connection)) {
      # Attempt to create the connection
      $connection = $this->createConnection($connectionName);

      if (!is_null($connection)) {
        $this->addConnection($connection);
      }
    }

    return $connection;
  }

  /**
   * Find a connection based on name
   *
   * If the $connectionName is empty/null we'll return the 1st
   * connection in the internal connection list (if there is one)
   *
   * @param  string   $connectionName       Name of the connection (from Config)
   * @return null | PdoConnection
   */
  public function findConnection(string $connectionName=null)
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
  public function removeConnection(string $name)
  {
    # Sanity check
    if (empty($name)) return false;

    $connection = $this->findConnection($name);

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
   * @return null | PdoConnection
   */
  protected function createConnection(string $connectionName)
  {
    # Try to find the connection in the internal list, if it was created already
    $connection = $this->connections[strtolower($connectionName)] ?? null;

    # If no connection found, and the $connectionName is empty, read in 1st one
    if (is_null($connection) && empty($connectionName)) {
      # Get connections from conf
      $arr = config()->get('connections');
      reset($arr);
      # Take the 1st connections name
      $connectionName = key($arr);
    }

    if (is_null($connection)) {
      # Get connection's params from conf
      $connConf = config()->get('connections.'.$connectionName);

      # Type="PDO"
      if ( strcasecmp($connConf['type'] ?? '','PDO')==0 ) {
        $className = '\\Spin\\Database\\Drivers\\'.ucfirst($connConf['type']).'\\'.ucfirst($connConf['driver']) ;

        try {
          # Create the PdoConnection
          $connection = new $className($connectionName, $connConf);
          logger()->debug( 'Created Connection', ['connection'=>$connection] );

        } catch (\Exception $e) {
          logger()->critical( $e->getMessage(), ['trace'=>$e->getTraceAsString()] );
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
