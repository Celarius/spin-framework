<?php declare(strict_types=1);

namespace Psr\Http\Message;

use Psr\Http\Message\ResponseInterface;

interface ResponseFactoryInterface
{
    /**
     * Create a new response.
     *
     * @param integer $code HTTP status code
     *
     * @return ResponseInterface
     */
    public function createResponse($code = 200);
}
