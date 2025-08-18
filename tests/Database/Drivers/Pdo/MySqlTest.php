<?php declare(strict_types=1);

namespace Spin\Tests\Database\Drivers\Pdo;

use PHPUnit\Framework\TestCase;
use Spin\Database\Drivers\Pdo\MySql;

class MySqlTest extends TestCase
{
  protected ?MySql $connection = null;
  protected array $connectionParams = [
    'type' => 'database',
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => 3306,
    'schema' => 'spin_test',
    'username' => 'spin_user',
    'password' => 'spin_password',
    'charset' => 'utf8mb4',
    'options' => []
  ];

  public function setUp(): void
  {
    try {
      $this->connection = new MySql('test_connection', $this->connectionParams);

      // Create a test table for our tests
      $this->connection->exec('DROP TABLE IF EXISTS test_table');
      $this->connection->exec('
        CREATE TABLE test_table (
          id INT AUTO_INCREMENT PRIMARY KEY,
          name VARCHAR(255) NOT NULL,
          value TEXT,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
      ');
    } catch (\Exception $e) {
      $this->markTestSkipped('MySQL server is not available: ' . $e->getMessage());
    }
  }

  public function tearDown(): void
  {
    if ($this->connection !== null) {
      try {
        // Clean up test table
        $this->connection->exec('DROP TABLE IF EXISTS test_table');
        // Disconnect
        $this->connection->disconnect();
      } catch (\Exception $e) {
        // Ignore cleanup errors
      }
    }
  }

  /**
   * Test MySQL connection creation
   */
  public function testConnectionCreation(): void
  {
    $this->assertNotNull($this->connection);
    $this->assertInstanceOf(MySql::class, $this->connection);
    $this->assertEquals('mysql', $this->connection->getDriver());
  }

  /**
   * Test DSN generation
   */
  public function testGetDsn(): void
  {
    $expectedDsn = 'mysql:host=127.0.0.1;port=3306;dbname=spin_test;charset=utf8mb4';
    $this->assertEquals($expectedDsn, $this->connection->getDsn());
  }

  /**
   * Test connection with custom options
   */
  public function testConnectionWithCustomOptions(): void
  {
    $customParams = $this->connectionParams;
    $customParams['options'] = [
      'ATTR_PERSISTENT' => false,
      'ATTR_ERRMODE' => 'ERRMODE_WARNING',
      'MYSQL_ATTR_INIT_COMMAND' => 'SET NAMES utf8mb4'
    ];

    $customConnection = new MySql('custom_connection', $customParams);
    $this->assertNotNull($customConnection);

    // Verify that MySQL specific attributes work
    $customConnection->disconnect();
  }

  /**
   * Test connection without port (should use default)
   */
  public function testConnectionWithoutPort(): void
  {
    $paramsNoPort = $this->connectionParams;
    $paramsNoPort['port'] = 0;

    $connection = new MySql('no_port_connection', $paramsNoPort);
    $expectedDsn = 'mysql:host=127.0.0.1;dbname=spin_test;charset=utf8mb4';
    $this->assertEquals($expectedDsn, $connection->getDsn());

    $connection->disconnect();
  }

  /**
   * Test default PDO options are set when none provided
   */
  public function testDefaultPdoOptions(): void
  {
    $paramsNoOptions = $this->connectionParams;
    unset($paramsNoOptions['options']);

    $connection = new MySql('default_options_connection', $paramsNoOptions);
    $options = $connection->getOptions();

    $this->assertArrayHasKey(\PDO::ATTR_PERSISTENT, $options);
    $this->assertArrayHasKey(\PDO::ATTR_ERRMODE, $options);
    $this->assertArrayHasKey(\PDO::ATTR_AUTOCOMMIT, $options);

    $connection->disconnect();
  }

  /**
   * Test connection metadata
   */
  public function testConnectionMetadata(): void
  {
    $this->assertEquals('test_connection', $this->connection->getName());
    $this->assertEquals('database', $this->connection->getType());
    $this->assertEquals('mysql', $this->connection->getDriver());
    $this->assertEquals('127.0.0.1', $this->connection->getHost());
    $this->assertEquals(3306, $this->connection->getPort());
    $this->assertEquals('spin_test', $this->connection->getSchema());
    $this->assertEquals('spin_user', $this->connection->getUsername());
    $this->assertEquals('spin_password', $this->connection->getPassword());
    $this->assertEquals('utf8mb4', $this->connection->getCharset());
  }

  /**
   * Test server and client version retrieval
   */
  public function testVersionInfo(): void
  {
    $serverVersion = $this->connection->getServerVersion();
    $clientVersion = $this->connection->getClientVersion();

    $this->assertIsString($serverVersion);
    $this->assertNotEmpty($serverVersion);
    $this->assertIsString($clientVersion);
    $this->assertNotEmpty($clientVersion);
  }

  /**
   * Test basic query execution with rawQuery
   */
  public function testRawQuery(): void
  {
    // Insert test data
    $this->connection->exec("INSERT INTO test_table (name, value) VALUES ('test1', 'value1'), ('test2', 'value2')");

    // Test SELECT query
    $results = $this->connection->rawQuery('SELECT * FROM test_table ORDER BY id');

    $this->assertIsArray($results);
    $this->assertCount(2, $results);
    $this->assertEquals('test1', $results[0]['name']);
    $this->assertEquals('value1', $results[0]['value']);
    $this->assertEquals('test2', $results[1]['name']);
    $this->assertEquals('value2', $results[1]['value']);
  }

  /**
   * Test parameterized query with rawQuery
   */
  public function testRawQueryWithParams(): void
  {
    // Insert test data
    $this->connection->exec("INSERT INTO test_table (name, value) VALUES ('test1', 'value1'), ('test2', 'value2')");

    // Test SELECT with parameters
    $results = $this->connection->rawQuery(
      'SELECT * FROM test_table WHERE name = :name',
      ['name' => 'test1']
    );

    $this->assertIsArray($results);
    $this->assertCount(1, $results);
    $this->assertEquals('test1', $results[0]['name']);
  }

  /**
   * Test rawExec for INSERT
   */
  public function testRawExecInsert(): void
  {
    $affectedRows = $this->connection->rawExec(
      'INSERT INTO test_table (name, value) VALUES (:name, :value)',
      ['name' => 'test_insert', 'value' => 'test_value']
    );

    $this->assertEquals(1, $affectedRows);

    // Verify insertion
    $results = $this->connection->rawQuery('SELECT * FROM test_table WHERE name = :name', ['name' => 'test_insert']);
    $this->assertCount(1, $results);
    $this->assertEquals('test_value', $results[0]['value']);
  }

  /**
   * Test rawExec for UPDATE
   */
  public function testRawExecUpdate(): void
  {
    // Insert initial data
    $this->connection->exec("INSERT INTO test_table (name, value) VALUES ('test_update', 'old_value')");

    // Update data
    $affectedRows = $this->connection->rawExec(
      'UPDATE test_table SET value = :value WHERE name = :name',
      ['name' => 'test_update', 'value' => 'new_value']
    );

    $this->assertEquals(1, $affectedRows);

    // Verify update
    $results = $this->connection->rawQuery('SELECT * FROM test_table WHERE name = :name', ['name' => 'test_update']);
    $this->assertEquals('new_value', $results[0]['value']);
  }

  /**
   * Test rawExec for DELETE
   */
  public function testRawExecDelete(): void
  {
    // Insert initial data
    $this->connection->exec("INSERT INTO test_table (name, value) VALUES ('test_delete', 'value')");

    // Delete data
    $affectedRows = $this->connection->rawExec(
      'DELETE FROM test_table WHERE name = :name',
      ['name' => 'test_delete']
    );

    $this->assertEquals(1, $affectedRows);

    // Verify deletion
    $results = $this->connection->rawQuery('SELECT * FROM test_table WHERE name = :name', ['name' => 'test_delete']);
    $this->assertCount(0, $results);
  }

  /**
   * Test transaction handling
   */
  public function testTransactions(): void
  {
    // Begin transaction
    $this->assertTrue($this->connection->beginTransaction());
    $this->assertTrue($this->connection->inTransaction());

    // Insert data
    $this->connection->exec("INSERT INTO test_table (name, value) VALUES ('transaction_test', 'value')");

    // Rollback
    $this->assertTrue($this->connection->rollBack());
    $this->assertFalse($this->connection->inTransaction());

    // Verify data was not committed
    $results = $this->connection->rawQuery('SELECT * FROM test_table WHERE name = :name', ['name' => 'transaction_test']);
    $this->assertCount(0, $results);

    // Test commit
    $this->connection->beginTransaction();
    $this->connection->exec("INSERT INTO test_table (name, value) VALUES ('commit_test', 'value')");
    $this->assertTrue($this->connection->commit());

    // Verify data was committed
    $results = $this->connection->rawQuery('SELECT * FROM test_table WHERE name = :name', ['name' => 'commit_test']);
    $this->assertCount(1, $results);
  }
   /**
    * Test automatic transaction handling in rawExec
    */
   public function testAutoTransactionHandling(): void
   {
     // Test with auto transactions enabled (default)
     $affectedRows = $this->connection->rawExec(
       'INSERT INTO test_table (name, value) VALUES (:name, :value)',
       ['name' => 'auto_trans', 'value' => 'value'],
       true
     );

     $this->assertEquals(1, $affectedRows);
  }

}


