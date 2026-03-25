<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Spin\Core\DotEnv;

class DotEnvTest extends TestCase
{
  // -------------------------------------------------------------------------
  // DotEnv::parse() — pure parser tests
  // -------------------------------------------------------------------------

  public function testParseBasicKeyValue(): void
  {
    $result = DotEnv::parse("KEY=value\n");
    $this->assertSame(['KEY' => 'value'], $result);
  }

  public function testParseDoubleQuotedValue(): void
  {
    $result = DotEnv::parse('KEY="hello world"');
    $this->assertSame(['KEY' => 'hello world'], $result);
  }

  public function testParseDoubleQuotedEscapeSequences(): void
  {
    $result = DotEnv::parse('KEY="line1\nline2"');
    $this->assertSame(['KEY' => "line1\nline2"], $result);
  }

  public function testParseSingleQuotedValueIsLiteral(): void
  {
    // Single-quoted values are literal — no escape processing
    $result = DotEnv::parse("KEY='literal \\n \$VAR'");
    $this->assertSame(['KEY' => 'literal \\n $VAR'], $result);
  }

  public function testParseExportKeyword(): void
  {
    $result = DotEnv::parse('export KEY=value');
    $this->assertSame(['KEY' => 'value'], $result);
  }

  public function testParseSkipsCommentLines(): void
  {
    $content = "# this is a comment\nKEY=value\n# another comment";
    $result = DotEnv::parse($content);
    $this->assertSame(['KEY' => 'value'], $result);
  }

  public function testParseSkipsBlankLines(): void
  {
    $content = "\nKEY=value\n\n";
    $result = DotEnv::parse($content);
    $this->assertSame(['KEY' => 'value'], $result);
  }

  public function testParseStripsInlineComment(): void
  {
    $result = DotEnv::parse('KEY=value # inline comment');
    $this->assertSame(['KEY' => 'value'], $result);
  }

  public function testParseEmptyValue(): void
  {
    $result = DotEnv::parse('KEY=');
    $this->assertSame(['KEY' => ''], $result);
  }

  public function testParseMultipleVars(): void
  {
    $content = "DB_HOST=localhost\nDB_PORT=3306\nDB_NAME=mydb";
    $result = DotEnv::parse($content);
    $this->assertSame([
      'DB_HOST' => 'localhost',
      'DB_PORT' => '3306',
      'DB_NAME' => 'mydb',
    ], $result);
  }

  public function testParseIgnoresLinesWithoutEquals(): void
  {
    $result = DotEnv::parse("NOT_A_VAR\nKEY=value");
    $this->assertSame(['KEY' => 'value'], $result);
  }

  // -------------------------------------------------------------------------
  // DotEnv::load() — file loading tests
  // -------------------------------------------------------------------------

  public function testLoadReturnZeroWhenFileNotFound(): void
  {
    $count = DotEnv::load('/nonexistent/path/that/does/not/exist');
    $this->assertSame(0, $count);
  }

  public function testLoadSetsEnvVar(): void
  {
    $dir = sys_get_temp_dir() . '/dotenv_test_' . uniqid();
    mkdir($dir);
    file_put_contents($dir . '/.env', "SPIN_TEST_LOAD_VAR=hello\n");

    // Ensure var is not already set
    putenv('SPIN_TEST_LOAD_VAR');
    unset($_ENV['SPIN_TEST_LOAD_VAR']);

    $count = DotEnv::load($dir);
    $this->assertSame(1, $count);
    $this->assertSame('hello', getenv('SPIN_TEST_LOAD_VAR'));
    $this->assertSame('hello', $_ENV['SPIN_TEST_LOAD_VAR']);

    // Cleanup
    unlink($dir . '/.env');
    rmdir($dir);
    putenv('SPIN_TEST_LOAD_VAR');
    unset($_ENV['SPIN_TEST_LOAD_VAR']);
  }

  public function testLoadDoesNotOverwriteExistingVarByDefault(): void
  {
    $dir = sys_get_temp_dir() . '/dotenv_test_' . uniqid();
    mkdir($dir);
    file_put_contents($dir . '/.env', "SPIN_TEST_NOWIPE=from_file\n");

    putenv('SPIN_TEST_NOWIPE=from_env');
    $_ENV['SPIN_TEST_NOWIPE'] = 'from_env';

    $count = DotEnv::load($dir, false);
    $this->assertSame(0, $count);
    $this->assertSame('from_env', getenv('SPIN_TEST_NOWIPE'));

    // Cleanup
    unlink($dir . '/.env');
    rmdir($dir);
    putenv('SPIN_TEST_NOWIPE');
    unset($_ENV['SPIN_TEST_NOWIPE']);
  }

  public function testLoadOverwritesExistingVarWhenRequested(): void
  {
    $dir = sys_get_temp_dir() . '/dotenv_test_' . uniqid();
    mkdir($dir);
    file_put_contents($dir . '/.env', "SPIN_TEST_WIPE=from_file\n");

    putenv('SPIN_TEST_WIPE=from_env');
    $_ENV['SPIN_TEST_WIPE'] = 'from_env';

    $count = DotEnv::load($dir, true);
    $this->assertSame(1, $count);
    $this->assertSame('from_file', getenv('SPIN_TEST_WIPE'));

    // Cleanup
    unlink($dir . '/.env');
    rmdir($dir);
    putenv('SPIN_TEST_WIPE');
    unset($_ENV['SPIN_TEST_WIPE']);
  }
}
