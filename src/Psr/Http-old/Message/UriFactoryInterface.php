<?php declare(strict_types=1);

namespace Psr\Http\Message;

use Psr\Http\Message\UriInterface;

interface UriFactoryInterface
{
    /**
     * Create a new URI.
     *
     * @param string $uri
     *
     * @return UriInterface
     *
     * @throws \InvalidArgumentException
     *  If the given URI cannot be parsed.
     */
    public function createUri($uri = '');
}
