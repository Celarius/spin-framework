<?php declare(strict_types=1);

/**
 * This file is part of the spin-framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  spin-framework
 */

namespace Tests\Core;

use PHPUnit\Framework\TestCase;
use Spin\Core\Controller;

/**
 * Tests for the base Controller HTTP verb handlers, focused on the QUERY method.
 */
class ControllerTest extends TestCase
{
  /**
   * Base handleQUERY() returns 405 Method Not Allowed when not overridden,
   * mirroring every other default verb handler.
   */
  public function testHandleQueryDefaultsTo405(): void
  {
    $controller = new class extends Controller {};

    $response = $controller->handleQUERY([]);

    $this->assertSame(405, $response->getStatusCode());
  }

  /**
   * A controller overriding handleQUERY() returns its own response — proving
   * QUERY is a first-class, dispatchable verb.
   */
  public function testHandleQueryCanBeOverridden(): void
  {
    $controller = new class extends Controller {
      public function handleQUERY(array $args)
      {
        return \response('ok', 200);
      }
    };

    $response = $controller->handleQUERY([]);

    $this->assertSame(200, $response->getStatusCode());
    $this->assertSame('ok', (string) $response->getBody());
  }
}
