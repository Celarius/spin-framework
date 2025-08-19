<?php declare(strict_types=1);

namespace Spin\tests\Container;

use PHPUnit\Framework\TestCase;

class ContainerObject
{
  protected string $property = '';

  public function getProperty(): string
  {
    return $this->property;
  }

  public function setProperty(string $value): void
  {
    $this->property = $value;
  }
}

class ContainerTest extends TestCase
{
  /**
   * Setup test
   */
  public function setUp(): void
  {
  }

  /** Test Container STRING */
  public function testContainerString(): void
  {
    $a = 'My Container String';

    # Set it
    container('string', $a);

    # Get it
    $b = container('string');

    $this->assertEquals($a, $b);
  }

  /** Test Container ARRAY */
  public function testContainerArray(): void
  {
    $a = [
      'a' => 'a value',
      'b' => 'b value',
      'c' => 'c value'
    ];

    # Set it
    container('array', $a);

    # Get it
    $b = container('array');

    $this->assertEquals($a, $b);
  }

  /** Test Container Object */
  public function testContainerObject(): void
  {
    $a = new ContainerObject();
    $a->setProperty('I get set, therefore I exist');

    # Set it
    container('object', $a);

    # Get it
    $b = container('object');

    $this->assertEquals($a->getProperty(), $b->getProperty());
  }

  /** Test Container Callable */
  public function testContainerAnonymousFunction(): void
  {
    # Set it
    container('anon',
      static function() {
        return 1234;
      }
    );

    $this->assertEquals(1234, container('anon'));
  }

  /** Test RequestId class */
  public function testContainerRequestIdClass(): void
  {
    # Create class
    container('requestId', new \Spin\Classes\RequestIdClass() );

    # set new value
    container('requestId')->setId('abc123');

    $this->assertEquals('abc123', (string)container('requestId'));
  }

}