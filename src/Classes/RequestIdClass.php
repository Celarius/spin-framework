<?php declare(strict_types=1);

/**
 * Class RequestIdClass
 *
 * Represents a unique request identifier for tracking and logging purposes within the Spin framework.
 * This class generates a unique ID (using md5 and microtime) for each request, which can be used for
 * tracing, debugging, and correlating logs. The ID can be regenerated or set manually as needed.
 *
 * @package Spin\Classes
 */
namespace Spin\Classes;

class RequestIdClass
{
  /**
   * The unique request ID value.
   *
   * @var string
   */
  protected $id = '';

  /**
   * Constructor.
   *
   * Initializes the request ID by generating a new unique value.
   */
  public function __construct()
  {
    $this->generateId();
  }

  /**
   * Returns the request ID as a string.
   *
   * @return string The request ID.
   */
  public function __toString()
  {
    return (string) $this->id;
  }

  /**
   * Generates a new unique request ID and sets it as the current ID.
   *
   * @return string The newly generated request ID.
   */
  public function generateId()
  {
    $this->id = \md5((string)\microtime(true));
    return $this->id;
  }

  /**
   * Sets the request ID to a specific value.
   *
   * @param string $value The value to set as the request ID.
   * @return $this
   */
  public function setId($value)
  {
    $this->id = $value;
    return $this;
  }
}
