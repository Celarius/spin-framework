<?php declare(strict_types=1);

/**
 * PDO Connection class
 *
 * @package   Spin
 */

namespace Spin\Database;

class PdoConnection extends \PDO implements PdoConnectionInterface
{
  /**
   * @var string Connection name
   */
  protected string $name = '';

  /**
   * @var string Connection type
   */
  protected string $type = '';

  /**
   * @var string Connection driver name ('MySql','Firebird','Sqlite'...)
   */
  protected string $driver = '';

  /**
   * @var string
   */
  protected string $schema = '';

  /**
   * @var string
   */
  protected string $host = '';

  /**
   * @var int
   */
  protected int $port = 0;

  /**
   * @var string
   */
  protected string $username = '';

  /**
   * @var string
   */
  protected string $password = '';

  /**
   * @var string
   */
  protected string $charset = '';

  /**
   * @var array PDO options array
   */
  protected array $options = [];

  /**
   * @var string Full DSN of PDO connection
   */
  protected string $dsn = '';

  /**
   * @var string Database Engine Version we connect to
   */
  protected string $serverVersion = '';

  /**
   * @var string Client Driver Version we connect to
   */
  protected string $clientVersion = '';


  /**
   * Constructor
   *
   * @param      string  $connectionName    Name of the connection
   * @param      array   $params            Array with connection parameters
   */
  public function __construct(string $connectionName, array $params = [])
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
    foreach ($pdoParams as $pdoOptionName => $pdoValue) {
      # Convert PDO-OPTION to a number
      $pdoOption = null;
      if (!\is_numeric($pdoOptionName)) {
        $pdoOption = \constant('\PDO::' . \strtoupper($pdoOptionName)); // PDO Option name constant
      }

      # Convert PDO-VALUE to a number
      if (\is_numeric($pdoValue)) {
        $pdoValue = (float) $pdoValue; // Its a number
      } else if (\is_bool($pdoValue)) {
        $pdoValue = (int) $pdoValue; // Its a BOOLEAN, convert to int
      } else if (!empty($pdoValue)) {
        if (\mb_substr($pdoOptionName,0,6) === 'MYSQL_') {
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
      if ($pdoOption) {
        $pdoOptions[(int)$pdoOption] = $pdoValue;
      }
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
    } catch (\Exception $e) {
      // error_log($e->getMessage().' | '.$e->getTraceAsString());
    }

    try {
      # Retrieve the DB Server Version (if supported)
      $this->setServerVersion($this->getAttribute(\PDO::ATTR_SERVER_VERSION));
    } catch (\Exception $e) {
      // error_log($e->getMessage().' | '.$e->getTraceAsString());
    }

    # There are sometimes errors here, but no exception so we clear them
    \error_clear_last();

    # Debug log
    \logger()->debug( 'Created Connection', ['connection' => $this->getName()] );
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
   * @inheritDoc
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
   * @inheritDoc
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
   * @inheritDoc
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * @inheritDoc
   */
  public function getType(): string
  {
    return $this->type;
  }

  /**
   * @inheritDoc
   */
  public function getDriver(): string
  {
    return $this->driver;
  }

  /**
   * @inheritDoc
   */
  public function getSchema(): string
  {
    return $this->schema;
  }

  /**
   * @inheritDoc
   */
  public function getHost(): string
  {
    return $this->host;
  }

  /**
   * @inheritDoc
   */
  public function getPort(): int
  {
    return $this->port;
  }

  /**
   * @inheritDoc
   */
  public function getUsername(): string
  {
    return $this->username;
  }

  /**
   * @inheritDoc
   */
  public function getPassword(): string
  {
    return $this->password;
  }

  /**
   * @inheritDoc
   */
  public function getCharset(): string
  {
    return $this->charset;
  }

  /**
   * @inheritDoc
   */
  public function getOptions(): array
  {
    return $this->options;
  }

  /**
   * @inheritDoc
   */
  public function getServerVersion(): string
  {
    return $this->serverVersion;
  }

  /**
   * @inheritDoc
   */
  public function getClientVersion(): string
  {
    return $this->clientVersion;
  }

  /**
   * @inheritDoc
   */
  public function setDsn(string $dsn): self
  {
    $this->dsn = $dsn;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setName(string $name): self
  {
    $this->name = $name;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setType(string $type): self
  {
    $this->type = $type;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setHost(string $host): self
  {
    $this->host = $host;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setPort(int $port): self
  {
    $this->port = $port;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setDriver(string $driver): self
  {
    $this->driver = $driver;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setSchema(string $schema): self
  {
    $this->schema = $schema;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setUsername(string $username): self
  {
    $this->username = $username;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setPassword(string $password): self
  {
    $this->password = $password;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setCharset(string $charset): self
  {
    $this->charset = $charset;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setServerVersion(string $serverVersion): self
  {
    $this->serverVersion = $serverVersion;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setClientVersion(string $clientVersion): self
  {
    $this->clientVersion = $clientVersion;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function setOptions(array $options): self
  {
    $this->options = $options;

    return $this;
  }

  /**
   * @inheritDoc
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
        foreach ($params as $bind => $value) {
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

    } catch (\Exception $e) {
      # If we had a local transaction, rollback
      if ($autoTransactions && $autoCommit) {
        $this->rollBack();
      }

      throw $e;
    }

    return $rows;
  }

  /**
   * @inheritDoc
   */
  public function rawExec(string $sql, array $params = [], bool $autoTransactions = true): ?int
  {
    # Sanity check
    if (empty($sql)) {
      return null;
    }

    $result = null;
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

    } catch (\Exception $e) {
      # If we had a local transaction, rollback
      if ($autoTransactions && $autoCommit) {
        $this->rollBack();
      }

      throw $e;
    }

    return $result;
  }

}
