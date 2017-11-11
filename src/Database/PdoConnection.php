<?php declare(strict_types=1);

namespace Spin\Database;

use \Spin\Database\PdoConnectionInterface;

abstract class PdoConnection extends \PDO implements PdoConnectionInterface
{
  /** @var string Connection name */
  protected $name = '';
  /** @var string Connection type */
  protected $type = '';
  /** @var string Connection driver ('MySql','Firebird','Sqlite'...) */
  protected $driver = '';
  protected $schema = '';
  protected $host = '';
  protected $port = 0;
  protected $username = '';
  protected $password = '';
  protected $charset = '';
  /** @var array PDO options array */
  protected $options = array();
  /** @var string Full DSN of PDO connection */
  protected $dsn = '';
  /** @var string Database Engine Version we connect to */
  protected $serverVersion = '';
  /** @var string Client Driver Version we connect to */
  protected $clientVersion = '';
  /** @var boolean Connection state. */
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
    if (isset($params['driver'])) $this->setDriver($params['driver']);
    $this->setHost($params['host'] ?? '');
    $this->setPort($params['port'] ?? '');
    $this->setSchema($params['schema'] ?? '');
    $this->setUsername($params['username'] ?? '');
    $this->setPassword($params['password'] ?? '');
    $this->setCharset($params['charset'] ?? '');

    # Get the PDO parameters from params
    $pdoParams = $params['options'] ?? [];

    $pdoOptions = [];
    # Convert the PDO params into PDO constants
    if ( count($pdoParams)>0 ) {
      foreach ($pdoParams as $idx => $p)
      {
        $pdoOption = strtoupper(key($p));
        $pdoValue = $pdoParams[$idx][key($p)];

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

    try {
      # Retreive the Client Library Version (if supported)
      $this->setClientVersion($this->getAttribute(\PDO::ATTR_CLIENT_VERSION));
    } catch (\Exception $e) {
      // error_log($e->getMessage().' | '.$e->getTraceAsString());
    }

    try {
      # Retreive the DB Server Version (if supported)
      $this->setServerVersion($this->getAttribute(\PDO::ATTR_SERVER_VERSION));
    } catch (\Exception $e) {
      // error_log($e->getMessage().' | '.$e->getTraceAsString());
    }

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
   * This method needs to be overridden in DB specific driver
   *
   * @return string       [description]
   */
  public function getDsn(): ?string
  {
    # Build the DSN
    $_dsn = $this->driver.':host='.$this->host.';port='.$this->port.';dbname='.$this->schema.';charset='.$this->charset;

    # Set it
    $this->setDsn($_dsn);

    return $this->dsn;
  }

  /**
   * Get the Name
   *
   * @return string
   */
  public function getName(): ?string
  {
    return $this->name;
  }

  /**
   * Get the Type
   *
   * @return string
   */
  public function getType(): ?string
  {
    return $this->type;
  }

  /**
   * Get the Driver
   *
   * @return string
   */
  public function getDriver(): ?string
  {
    return $this->driver;
  }

  /**
   * Get the Schema
   *
   * @return string
   */
  public function getSchema(): ?string
  {
    return $this->schema;
  }

  /**
   * Get the Host
   *
   * @return string
   */
  public function getHost(): ?string
  {
    return $this->host;
  }

  /**
   * Get the Port
   *
   * @return string
   */
  public function getPort(): ?int
  {
    return $this->port;
  }

  /**
   * Get Username
   *
   * @return string
   */
  public function getUsername(): ?string
  {
    return $this->username;
  }

  /**
   * Get Password
   *
   * @return string
   */
  public function getPassword(): ?string
  {
    return $this->password;
  }

  /**
   * Get Charset
   *
   * @return string
   */
  public function getCharset(): ?string
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
    return $this->options;
  }

  /**
   * Get Server Version
   *
   * @return array
   */
  public function getServerVersion(): ?string
  {
    return $this->serverVersion;
  }

  /**
   * Get Client Version
   *
   * @return array
   */
  public function getClientVersion(): ?string
  {
    return $this->clientVersion;
  }

  /**
   * Set DSN connection string
   *
   * @param [type] $dsn           [description]
   */
  public function setDsn(?string $dsn)
  {
    $this->dsn = $dsn;

    return $this;
  }

  /**
   * Set Connection Name
   *
   * @param   string $name
   * @return  self
   */
  public function setName(?string $name)
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
  public function setType(?string $type)
  {
    $this->type = $type;

    return $this;
  }

  /**
   * Set Connection host
   *
   * @param   string $host
   * @return  self
   */
  public function setHost(?string $host)
  {
    $this->host = $host;

    return $this;
  }

  /**
   * Set port
   *
   * @param   string $port
   * @return  self
   */
  public function setPort($port)
  {
    $this->port = $port;

    return $this;
  }

  /**
   * Set Driver
   *
   * @param   string $driver
   * @return  self
   */
  public function setDriver(?string $driver)
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
  public function setSchema(?string $schema)
  {
    $this->schema = $schema;

    return $this;
  }

  /**
   * Set Username
   *
   * @param string $username      [description]
   */
  public function setUsername(?string $username)
  {
    $this->username = $username;

    return $this;
  }

  /**
   * Set Password
   *
   * @param string $password      [description]
   */
  public function setPassword(?string $password)
  {
    $this->password = $password;

    return $this;
  }

  /**
   * Set Charset
   *
   * @param string $charset       Charset to use
   */
  public function setCharset(?string $charset)
  {
    $this->charset = $charset;

    return $this;
  }

  /**
   * Set ServerVersion
   *
   * @param string $serverVersio
   */
  public function setServerVersion(?string $serverVersion)
  {
    $this->serverVersion = $serverVersion;

    return $this;
  }

  /**
   * Set clientVersion
   *
   * @param string $serverVersio
   */
  public function setClientVersion(?string $clientVersion)
  {
    $this->clientVersion = $clientVersion;

    return $this;
  }

  /**
   * Set Connection Options
   *
   * @param array $options        [description]
   */
  public function setOptions(array $options)
  {
    $this->options = $options;

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
