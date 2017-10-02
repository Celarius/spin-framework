<?php declare(strict_types=1);

/**
 * Generic PDO Database Connection
 *
 */

namespace Spin\Core\Database;

use \Spin\Core\Database\PdoConnectionInterface;

/*
POSTGRESQL:
  // host=localhost
  // port=5432
  // dbname=C:\db\banco.gdb
  $connection = new PDO("pgsql:host=192.168.137.1;port=5432;dbname=anydb", $user, $pass,
    array(
      PDO::ATTR_PERSISTENT => true
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_AUTOCOMMIT => false
    )
  );
*/

abstract class PdoConnection extends \PDO implements PdoConnectionInterface
{
  protected $name = ''; // Connection name
  protected $type = ''; // Connection Type
  protected $driver = ''; // Connection Driver ('MySql','Firebird','Sqlite'...)

  protected $schema = '';
  protected $host = '';
  protected $port = 0;
  protected $username = '';
  protected $password = '';
  protected $charset = '';
  protected $pdo_options = array(); // PDO options array

  protected $pdo_dsn = '';
  protected $version = ''; // Database Version we connect to

  /** @var boolean True=Connected, False=Not connected */
  protected $connected = false;


  /**
   * Constructor
   *
   * @param string $connectionName [description]
   * @param array  $params         [description]
   */
  public function __construct(string $connectionName, array $params=[])
  {
    # Extract the needed parameters
    $this->setName($connectionName);
    $this->setType($params['type'] ?? '');
    $this->setDriver($params['driver'] ?? '');
    $this->host = ($params['host'] ?? '');
    $this->port = ($params['port'] ?? '');
    $this->setSchema($params['schema'] ?? '');
    $this->setUsername($params['username'] ?? '');
    $this->setPassword($params['password'] ?? '');
    $this->setCharset($params['charset'] ?? '');

    # Get the PDO parameters from params
    $pdoParams = $params['options'] ?? [];

    $pdoOptions = [];
    # Convert the PDO params into PDO constants
    if ( count($pdoParams)>0 ) {
      foreach ($pdoParams as $p)
      {
        $pdoOption = strtoupper(key($p));
        $pdoValue = trim(reset($p));

        # Convert to PDO constants
        $k = constant('\PDO::'.$pdoOption); // PDO Option
        if ( !is_numeric($pdoValue) && !empty($pdoValue) ) {
          $v = @constant('\PDO::'.$pdoValue);  // PDO constant
        } else if (!empty($pdoValue)) {
          $v = $pdoValue; // Its a string
        } else {
          $v = 0; // false
        }
        # Set the Option
        $pdoOptions[ $k ] = $v;
      }
    }

    # Default PDO options for all drivers if none given
    if (count($pdoOptions)==0) {
      $pdoOptions =
        array(
          \PDO::ATTR_PERSISTENT => TRUE,
          \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
          \PDO::ATTR_AUTOCOMMIT => FALSE
        );
    }

    # Set PDO Options
    $this->setOptions($pdoOptions);

    # Parent Constructor (PDO Class)
    parent::__construct($this->getDsn(),$this->getUsername(),$this->getPassword(),$this->getOptions());

    # Retreive the DB Engine Version (if supported)
    $this->version = $this->getAttribute(\PDO::ATTR_SERVER_VERSION);

    # Set connected
    $this->connected = true;
  }

  /**
   * Destrctor
   */
  public function __destruct()
  {
    # Disconnect
    $this->disconnect();
  }

  /**
   * Connect to Database
   *
   * This does not work in PDO as the connection is always open as long as the object (this) exists.
   *
   * @return [type] [description]
   */
  public function connect(): bool
  {
    return $this->connected();
  }

  /**
   * Disconnect from database
   *
   * This does not work in PDO since there is no disconnect() feature in PDO
   *
   * @return [type] [description]
   */
  public function disconnect(): bool
  {
    # Rollback any open transactions
    if ($this->inTransaction()) $this->rollback();

    return true;
  }

  /**
   * Checks if Connected to a Database
   *
   * @return bool         True for connected, false if not
   */
  public function connected(): bool
  {
    return $this->connected;
  }

  /**
   * Get DSN - Return the default formatted DSN
   *
   * This method needs to be overridden in DB Driver specific files
   *
   * @return string       [description]
   */
  public function getDsn(): string
  {
    # Build the DSN
    $_dsn = $this->driver.':host='.$this->host.';port='.$this->port.';dbname='.$this->schema.';charset='.$this->charset;
    # Set it
    $this->setDsn($_dsn);

    return $this->pdo_dsn;
  }

  /**
   * Get the Name
   *
   * @return string
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * Get the Type
   *
   * @return string
   */
  public function getType(): string
  {
    return $this->type;
  }

  /**
   * Get the Driver
   *
   * @return string
   */
  public function getDriver(): string
  {
    return $this->driver;
  }

  /**
   * Get the Schema
   *
   * @return string
   */
  public function getSchema(): string
  {
    return $this->schema;
  }

  /**
   * Get the Host
   *
   * @return string
   */
  public function getHost(): string
  {
    return $this->host;
  }

  /**
   * Get the Port
   *
   * @return string
   */
  public function getPort(): int
  {
    return $this->port;
  }


  /**
   * Get Username
   *
   * @return string
   */
  public function getUsername(): string
  {
    return $this->username;
  }

  /**
   * Get Password
   *
   * @return string
   */
  public function getPassword(): string
  {
    return $this->password;
  }

  /**
   * Get Charset
   *
   * @return string
   */
  public function getCharset(): string
  {
    return $this->charset;
  }

  /**
   * Get Options
   *
   * @return array
   */
  public function getOptions(): array
  {
    return $this->pdo_options;
  }

  /**
   * Get Version
   *
   * @return array
   */
  public function getVersion(): string
  {
    return $this->version;
  }

  /**
   * Set Connection Name
   *
   * @param   string $name
   * @return  self
   */
  public function setName(string $name)
  {
    $this->name = $name;

    return $this;
  }

  /**
   * Set Type
   *
   * @param   string $type
   * @return  self
   */
  public function setType(string $type)
  {
    $this->type = $type;

    return $this;
  }

  /**
   * Set Driver
   *
   * @param   string $driver
   * @return  self
   */
  public function setDriver(string $driver)
  {
    $this->driver = $driver;

    return $this;
  }

  /**
   * Set Schema
   *
   * @param   string $schema
   * @return  self
   */
  public function setSchema(string $schema)
  {
    $this->schema = $schema;

    return $this;
  }

  /**
   * Set DSN connection string
   *
   * @param [type] $dsn           [description]
   */
  public function setDsn(string $dsn)
  {
    $this->pdo_dsn = $dsn;

    return $this;
  }

  /**
   * Set Username
   *
   * @param string $username      [description]
   */
  public function setUsername(string $username)
  {
    $this->username = $username;

    return $this;
  }

  /**
   * Set Password
   *
   * @param string $password      [description]
   */
  public function setPassword(string $password)
  {
    $this->password = $password;

    return $this;
  }

  /**
   * Set Charset
   *
   * @param string $charset       Charset to use
   */
  public function setCharset(string $charset)
  {
    $this->charset = $charset;

    return $this;
  }

  /**
   * Set Connection Options
   *
   * @param array $options        [description]
   */
  public function setOptions(array $options)
  {
    $this->pdo_options = $options;

    return $this;
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
    $rows = [];

    # Sanity check
    if (empty($sql)) {
      return $rows;
    }

    # Obtain transaction, unelss already in a transaction
    $autoCommit = $this->beginTransaction();

    # Prepare
    if ($sth = $this->prepare($sql)) {

      # Binds
      foreach ($params as $bind=>$value) {
        $sth->bindValue( ':'.ltrim($bind,':'), $value);
      }

      # Execute statement
      if ($sth->execute()) {
        $rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
      }

      # Close the cursor
      $sth->closeCursor();
    }

    # If we had a loacl transaction, commit it
    if ($autoCommit) $this->commit();

    return $rows;
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
    $result = false;

    # Sanity check
    if (empty($sql)) {
      return $result;
    }

    # Obtain transaction, unelss already in a transaction
    $autoCommit = $this->beginTransaction();

    # Prepare
    if ($sth = $this->prepare($sql)) {
      # Binds
      foreach ($params as $bind=>$value) {
        $sth->bindValue( ':'.ltrim($bind,':'), $value);
      }

      # Execute statement
      if ($sth->execute()) {
        $result = $this->rowCount() > 0;
      }

      # Close cursor
      $sth->closeCursor();
    }

    # If we had a loacl transaction, commit it
    if ($autoCommit) $this->commit();

    return $result;
  }
}
