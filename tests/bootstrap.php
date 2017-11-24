<?php declare(strict_types=1);

/**
 * This file is part of the spin-framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  spin-framework
 */

use PHPUnit\Framework\TestCase;

date_default_timezone_set('UTC');
require __DIR__.'/../vendor/autoload.php';

# Create application
$app = new \Spin\Application( realpath(__DIR__) );
