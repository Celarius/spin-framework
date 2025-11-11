
<?php
use PHPUnit\Framework\TestCase;
use Spin\Core\Config;

class ConfigTestReplaceEnvMacros extends Config {
	// Expose protected method for testing
	public function publicReplaceEnvMacros(array $input, array $envVars): array {
		return $this->replaceEnvMacros($input, $envVars);
	}
}

class ConfigTest extends TestCase
{
	public function testReplaceEnvMacrosReplacesEnvVars() {
		$config = new ConfigTestReplaceEnvMacros('', '');
		$input = [
			'db' => [
				'host' => '${env:DB_HOST}',
				'user' => '${env:DB_USER}',
				'pass' => 'static',
			],
			'plain' => 'no-macro',
		];
		$envVars = [
			'DB_HOST' => 'localhost',
			'DB_USER' => 'root',
		];

		$expected = [
			'db' => [
				'host' => 'localhost',
				'user' => 'root',
				'pass' => 'static',
			],
			'plain' => 'no-macro',
		];

		$result = $config->publicReplaceEnvMacros($input, $envVars);

        $this->assertEquals($expected, $result);
	}

	public function testReplaceEnvMacrosMissingEnvVar() {
		$config = new ConfigTestReplaceEnvMacros('', '');
		$input = [
			'api' => '${env:API_KEY}',
		];
		$envVars = [];
		$expected = [
			'api' => '',
		];
		$result = $config->publicReplaceEnvMacros($input, $envVars);
		$this->assertEquals($expected, $result);
	}

	public function testReplaceEnvMacrosNestedArrays() {
		$config = new ConfigTestReplaceEnvMacros('', '');
		$input = [
			'outer' => [
				'inner' => '${env:VAR}',
			],
		];
		$envVars = [ 'VAR' => 'value' ];
		$expected = [
			'outer' => [
				'inner' => 'value',
			],
		];
		$result = $config->publicReplaceEnvMacros($input, $envVars);
		$this->assertEquals($expected, $result);
	}

	public function testReplaceEnvMacrosMultipleMacrosInString() {
		$config = new ConfigTestReplaceEnvMacros('', '');
		$input = [
			'multi' => 'User: ${env:USER}, Host: ${env:HOST}',
		];
		$envVars = [ 'USER' => 'admin', 'HOST' => 'server' ];
		$expected = [
			'multi' => 'User: admin, Host: server',
		];
		$result = $config->publicReplaceEnvMacros($input, $envVars);
		$this->assertEquals($expected, $result);
	}
}
