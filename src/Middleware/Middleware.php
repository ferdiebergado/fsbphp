<?php

namespace FSB\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;

class Middleware
{
    protected $response;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->response = $responseFactory->createResponse();
    }
}
