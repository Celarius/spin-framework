<?php declare(strict_types=1);

/**
 * JWT
 *
 * JSON Web Token Class
 *
 * @link     https://github.com/firebase/php-jwt
 * @package  Spin
 */

namespace Spin\Helpers;

use \Firebase\JWT\JWT AS JWTClient;
use \Spin\Helpers\JWTInterface;

class JWT extends JWTClient implements JWTInterface
{
}
