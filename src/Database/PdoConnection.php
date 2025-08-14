<?php declare(strict_types=1);

/**
 * PDO Connection class
 *
 * @package   Spin
 */

namespace Spin\Database;

use \Exception;
use \Spin\Database\PdoConnectionInterface;

abstract class PdoConnection extends \PDO implements PdoConnectionInterface
{
  /** @var  string          Connection name */
  protected string $name = '';

  /** @var  string          Connection type */
  protected string $type = '';

  /** @var  string          Connection driver name ('MySql','Firebird','Sqlite'...) */
  protected string $driver = '';

  /** @var  bool            Connection state. */
  protected bool $connected = false;

  /** @var  string          Connection property string */
  protected string $schema = '';

  /** @var  string          Connection property string */
  protected string $host = '';

  /** @var  int             Connection property string */
  protected int $port = 0;

  /** @var  string          Connection property string */
  protected string $username = '';

  /** @var  string          Connection property string */
  protected string $password = '';

  /** @var  string          Connection property string */
  protected string $charset = '';

  /** @var array            PDO options array */
  protected array $options = [];

  /** @var string           Full DSN of PDO connection */
  protected string $dsn = '';

  /** @var string           Database Engine Version we connect to */
  protected string $serverVersion = '';

  /** @var string           Client Driver Version we connect to */
  protected string $clientVersion = '';


  /**
   * Constructor
   *
   * @param      string  $connectionName    Name of the connection
   * @param      array   $params            Array with connection parameters
   */
  public function __construct(string $connectionName, array $params=[])
  {
    # Extract the needed parameters
    $this->setName($connectionName);
    $this->setType($params['type'] ?? '');
    $this->setDriver($params['driver'] ?? '');
    $this->setHost($params['host'] ?? '');
    $this->setPort($params['port'] ?? 0);
    $this->setSchema($params['schema'] ?? '');
    $this->setUsername($params['username'] ?? '');
    $this->setPassword($params['password'] ?? '');
    $this->setCharset($params['charset'] ?? '');

    # Get the PDO parameters from params
    $pdoParams = $params['options'] ?? [];

    $pdoOptions = [];

    # Convert the PDO params into PDO constants
    foreach ($pdoParams as $pdoOptionName => $pdoValue)
    {
      # Convert PDO-OPTION to a number
      if (!\is_numeric($pdoOptionName)) {
        $pdoOption = \constant('\PDO::'.\strtoupper($pdoOptionName)); // PDO Option name constant
      }

      # Convert PDO-VALUE to a number
      if (\is_numeric($pdoValue)) {
        $pdoValue = (float) $pdoValue; // Its a number

      } else if (\is_bool($pdoValue)) {
        $pdoValue = (int) $pdoValue; // Its a BOOLEAN, convert to int

      } else if (!empty($pdoValue)) {

        if (\strtoupper(\mb_substr($pdoOptionName,0,6)) === 'MYSQL_') {
          // MySQL values, do not prepend with `PDO::`
          // Just fallthrough with the value as is
          $pdoValue = (string) $pdoValue;
        } else {
          $pdoValue = \constant('\PDO::'.\strtoupper($pdoValue)) ?? 0; // PDO value name constant
        }

      } else {
        $pdoValue = 0; // false
      }

      # Set the Option
      $pdoOptions[ (int)$pdoOption ] = $pdoValue;
    }

    # Default PDO options for all drivers if none given
    if (\count($pdoOptions) === 0) {
      $pdoOptions = [
          \PDO::ATTR_PERSISTENT => 1,
          \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
          \PDO::ATTR_AUTOCOMMIT => 0
        ];
    }

    # Set PDO Options
    $this->setOptions($pdoOptions);

    # Parent Constructor (PDO Class)
    parent::__construct($this->getDsn(),$this->getUsername(),$this->getPassword(),$this->getOptions());

    try {
      # Retrieve the Client Library Version (if supported)
      $this->setClientVersion($this->getAttribute(\PDO::ATTR_CLIENT_VERSION));
    } catch (Exception $e) {
      // Intentionally suppressed
      // error_log($e->getMessage().' | '.$e->getTraceAsString());
    }

    try {
      # Retrieve the DB Server Version (if supported)
      $this->setServerVersion($this->getAttribute(\PDO::ATTR_SERVER_VERSION));
    } catch (Exception $e) {
      // Intentionally suppressed
      // error_log($e->getMessage().' | '.$e->getTraceAsString());
    }

    # There are sometimes errors here, but no exception so we clear them
    \error_clear_last();

    # Debug log
    \logger()->debug( 'Created Connection', ['connection'=>$this->getName()] );

    # Set connected
    $this->connected = true;
  }

  /**
   * Destructor
   */
  public function __destruct()
  {
    # Disconnect
    $this->disconnect();
  }

  /**
   * Disconnect from database
   *
   * This does not work in PDO since there is no disconnect() feature in PDO
   *
   * @return     bool
   */
  public function disconnect(): bool
  {
    # Rollback any open transactions
    if ($this->inTransaction()) {
      $this->rollback();
    }

    return true;
  }

  /**
   * Checks if Connected to a Database
   *
   * @return     bool  True for connected, false if not
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
   * @return     string
   */
  public function getDsn(): string
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
   * @return     string
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * Get the Type
   *
   * @return     string
   */
  public function getType(): string
  {
    return $this->type;
  }

  /**
   * Get the Driver
   *
   * @return     string
   */
  public function getDriver(): string
  {
    return $this->driver;
  }

  /**
   * Get the Schema
   *
   * @return     string
   */
  public function getSchema(): string
  {
    return $this->schema;
  }

  /**
   * Get the Host
   *
   * @return     string
   */
  public function getHost(): string
  {
    return $this->host;
  }

  /**
   * Get the Port
   *
   * @return     int
   */
  public function getPort(): int
  {
    return $this->port;
  }

  /**
   * Get Username
   *
   * @return     string
   */
  public function getUsername(): string
  {
    return $this->username;
  }

  /**
   * Get Password
   *
   * @return     string
   */
  public function getPassword(): string
  {
    return $this->password;
  }

  /**
   * Get Charset
   *
   * @return     string
   */
  public function getCharset(): string
  {
    return $this->charset;
  }

  /**
   * Get Options
   *
   * @return     array
   */
  public function getOptions(): array
  {
    return $this->options;
  }

  /**
   * Get Server Version
   *
   * @return     string
   */
  public function getServerVersion(): string
  {
    return $this->serverVersion;
  }

  /**
   * Get Client Version
   *
   * @return     string
   */
  public function getClientVersion(): string
  {
    return $this->clientVersion;
  }

  /**
   * Set DSN connection string
   *
   * @param string $dsn
   *
   * @return self
   */
  public function setDsn(string $dsn): self
  {
    $this->dsn = $dsn;

    return $this;
  }

  /**
   * Set Connection Name
   *
   * @param      string  $name
   *
   * @return     self
   */
  public function setName(string $name): self
  {
    $this->name = $name;

    return $this;
  }

  /**
   * Set Type
   *
   * @param      string  $type
   *
   * @return     self
   */
  public function setType(string $type): self
  {
    $this->type = $type;

    return $this;
  }

  /**
   * Set Connection host
   *
   * @param      string  $host
   *
   * @return     self
   */
  public function setHost(string $host): self
  {
    $this->host = $host;

    return $this;
  }

  /**
   * Set port
   *
   * @param      int    $port
   *
   * @return     self
   */
  public function setPort(int $port): self
  {
    $this->port = $port;

    return $this;
  }

  /**
   * Set Driver
   *
   * @param      string  $driver
   *
   * @return     self
   */
  public function setDriver(string $driver): self
  {
    $this->driver = $driver;

    return $this;
  }

  /**
   * Set Schema
   *
   * @param      string  $schema
   *
   * @return     self
   */
  public function setSchema(string $schema): self
  {
    $this->schema = $schema;

    return $this;
  }

  /**
   * Set Username
   *
   * @param      string  $username
   *
   * @return     self
   */
  public function setUsername(string $username): self
  {
    $this->username = $username;

    return $this;
  }

  /**
   * Set Password
   *
   * @param      string  $password
   *
   * @return     self
   */
  public function setPassword(string $password): self
  {
    $this->password = $password;

    return $this;
  }

  /**
   * Set Charset
   *
   * @param      string  $charset  Charset to use
   *
   * @return     self
   */
  public function setCharset(string $charset): self
  {
    $this->charset = $charset;

    return $this;
  }

  /**
   * Set ServerVersion
   *
   * @param      string  $serverVersion
   *
   * @return     self
   */
  public function setServerVersion(string $serverVersion): self
  {
    $this->serverVersion = $serverVersion;

    return $this;
  }

  /**
   * Set clientVersion
   *
   * @param      string  $clientVersion  The client version
   *
   * @return     self
   */
  public function setClientVersion(string $clientVersion): self
  {
    $this->clientVersion = $clientVersion;

    return $this;
  }

  /**
   * Set Connection Options
   *
   * @param      array  $options
   *
   * @return     self
   */
  public function setOptions(array $options): self
  {
    $this->options = $options;

    return $this;
  }

  /**
   * Execute a SELECT statement
   *
   * @param   string  $sql                SQL statement to execute (SELECT ...)
   * @param   array   $params             Array with Bind params
   * @param   bool    $autoTransactions   Optional. TRUE enables automatic transaction handling
   *
   * @return  array                       Array with fetched rows
   *
   * @throws  Exception
   */
  public function rawQuery(string $sql, array $params=[], bool $autoTransactions=true): array
  {
    $rows = [];

    # Sanity check
    if (empty($sql)) {
      return $rows;
    }

    try {
      # Obtain transaction, unless already in a transaction
      $autoCommit = false;
      if ($autoTransactions && !$this->inTransaction()) {
        $autoCommit = $this->beginTransaction();
      }

      # Prepare
      if ($sth = $this->prepare($sql)) {

        # Binds
        foreach ($params as $bind => $value)
        {
          if (\is_int($value)) {
            $sth->bindValue( ':'.\ltrim($bind,':'), (int)$value, \PDO::PARAM_INT); // INT !
          } else
          if (\is_bool($value)) {
            $sth->bindValue( ':'.\ltrim($bind,':'), (bool)$value, \PDO::PARAM_BOOL);
          } else
          if (\is_null($value)) {
            $sth->bindValue( ':'.\ltrim($bind,':'), null, \PDO::PARAM_NULL);
          } else {
            $sth->bindValue( ':'.\ltrim($bind,':'), $value, \PDO::PARAM_STR);
          }
        }

        # Execute statement
        if ($sth->execute()) {
          $rows = $sth->fetchAll(\PDO::FETCH_ASSOC);
        }

        # Close the cursor
        $sth->closeCursor();
      }

      # If we had a local transaction, commit it
      if ($autoTransactions && $autoCommit) {
        $this->commit();
      }

    } catch (Exception $e) {
      # If we had a local transaction, rollback
      if ($autoTransactions && $autoCommit) {
        $this->rollBack();
      }

      throw $e;
    }

    return $rows;
  }

  /**
   * Execute an INSERT, UPDATE or DELETE statement
   *
   * @param   string  $sql                SQL statement to execute (INSERT, UPDATE, DELETE ...)
   * @param   array   $params             Array with Bind params
   * @param   bool    $autoTransactions   Optional. TRUE enables automatic transaction handling
   *
   * @return  ?int                       `null` or number of rows affected
   *
   * @throws  Exception
   */
  public function rawExec(string $sql, array $params = [], bool $autoTransactions = true): ?int
  {
    $result = null;

    # Sanity check
    if (empty($sql)) {
      return $result;
    }

    try {
      # Obtain transaction, unless already in a transaction
      $autoCommit = false;
      if ($autoTransactions && !$this->inTransaction()) {
        $autoCommit = $this->beginTransaction();
      }

      # Prepare
      if ($sth = $this->prepare($sql)) {
        # Binds
        foreach ($params as $bind=>$value) {
          $sth->bindValue( ':'.\ltrim($bind,':'), $value);
        }

        # Execute statement
        if ($sth->execute()) {
          $result = $sth->rowCount();
        }

        # Close cursor
        $sth->closeCursor();
      }

      # If we had a local transaction, commit it
      if ($autoTransactions && $autoCommit) {
        $this->commit();
      }

    } catch (Exception $e) {
      # If we had a local transaction, rollback
      if ($autoTransactions && $autoCommit) {
        $this->rollBack();
      }

      throw $e;
    }

    return $result;
  }

}
