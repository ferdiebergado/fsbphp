<?php
namespace FSB\Emitter;

use Psr\Http\Message\ResponseInterface;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;

class ConditionalEmitter implements EmitterInterface
{
    private $emitter;

    public function __construct(EmitterInterface $emitter)
    {
        $this->emitter = $emitter;
    }

    public function emit(ResponseInterface $response) : bool
    {
        if (!$response->hasHeader('Content-Disposition')
            && !$response->hasHeader('Content-Range')) {
            return false;
        }
        return $this->emitter->emit($response);
    }
}
