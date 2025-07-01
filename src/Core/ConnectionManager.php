<?php declare(strict_types=1);

/**
 * ConnectionManager
 *
 * Manages all Database Connections
 *
 * @package    Spin
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
  /** @var  array         List of Instantiated Connections */
  protected $connections = [];

  /**
   * Get or Create a connection
   *
   * @param      null|string  $name    Name of the connection
   * @param      array   $params  The connection parameters
   *
   * @return     null|PdoConnection
   */
  public function getConnection(?string $name=null, array $params=[])
  {
    # Find the connection - null if it's not created
    $connection = $this->findConnection($name);

    if (\is_null($connection)) {
      # Create the connection
      $connection = $this->createConnection($name, $params);

      if (!\is_null($connection)) {
        $this->addConnection($connection);
      }
    }

    return $connection;
  }

  /**
   * Find a connection in the pool
   *
   * If the $name is empty/null we'll return the 1st connection in the internal
   * connection list (if there is one)
   *
   * @param      null|string  $name   Name of the connection (from Config)
   *
   * @return     null|PdoConnection
   */
  public function findConnection(?string $name=null)
  {
    if ( empty($name) ) {
      # Take first available connection
      $connection = \reset($this->connections);
      if ($connection === false)
        return null;
    } else {
      # Attempt to find the connection from the pool
      $connection = ( $this->connections[\strtolower($name)] ?? null);
    }

    return $connection;
  }

  /**
   * Adds a Connection to the Pool
   *
   * @param      PdoConnectionInterface  $connection  [description]
   *
   * @return     PdoConnection
   */
  public function addConnection(PdoConnectionInterface $connection)
  {
    $this->connections[\strtolower($connection->getName())] = $connection;

    return $connection;
  }

  /**
   * Remove a connection from the pool
   *
   * @param      string  $name   Name of connection to remove
   *
   * @return     bool
   */
  public function removeConnection(string $name)
  {
    # Sanity check
    if (empty($name)) return false;

    $connection = $this->findConnection($name);

    if ($connection) {
      $connection->disconnect();
      unset( $this->connections[\strtolower($connection->getName())] );
      unset($connection);
      $connection = null;
    }

    return \is_null($connection);
  }

  /**
   * Creates a Connection based on the $connectionName and optional $params
   *
   * Finds the corresponding $connectionName in the config and uses it to
   * instanciate a connection. If the ConnectionName is empty, we will use the
   * 1st available record in the Connections list.
   *
   * If $connectionName is not found, and $params is populated, the $params are
   * be used to create the connection, which is added with $connectioName to the
   * internal list. The format is the same as in the `config-{env}.json` file
   *
   * @param      string  $connectionName  A name for the connection
   * @param      array   $params          The connection parameters
   *
   * @return     null|PdoConnection
   */
  protected function createConnection(string $connectionName, array $params=[])
  {
    # Try to find the connection in the internal list, if it was created already
    $connection = $this->connections[\strtolower($connectionName)] ?? null;

    # If no connection found, and the $connectionName is empty, read in 1st one
    if (\is_null($connection) && empty($connectionName)) {
      # Get connections from conf
      $arr = \config()->get('connections');
      \reset($arr);
      # Take the 1st connections name
      $connectionName = key($arr);
    }

    if (\is_null($connection)) {
      # Get connection's params from conf - unelss they were provided
      if (\count($params)==0) {
        $params = \config()->get('connections.'.$connectionName);
      }

      # Type="PDO"
      if ( \strcasecmp($params['type'] ?? '','PDO')==0 ) {
        # Build the Classname
        $className = '\\Spin\\Database\\Drivers\\'.\ucfirst($params['type'] ?? '').'\\'.($params['driver'] ?? '') ;

        try {
          # Create the PdoConnection
          $connection = new $className($connectionName, $params);
        } catch (\Exception $e) {
          # replace host/password from trace
          $beforeTraceLength  = \strlen($e->getTraceAsString());
          $trace              = $e->getTraceAsString();
          $trace              = \str_replace($params['password'], "", $trace);

          # throw new exception only if trace is modified
          if($beforeTraceLength != \strlen($trace)) {
            # new exception
            throw new \Exception($e->getMessage(), $e->getCode());
          }
          throw $e;
        }
      }
    }

    return $connection;
  }

  /**
   * Get array of PdoConnection
   *
   * @return     array
   */
  public function getConnections(): array
  {
    return $this->connections;
  }

}
