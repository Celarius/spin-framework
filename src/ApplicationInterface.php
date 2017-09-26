<?php declare(strict_types=1);

/**
 * Spin Framework
 *
 * @package   Spin
 */

namespace Spin;

interface ApplicationInterface
{
  function getAppPath(): string;
  function getStoragePath(): string;
}