<?php

namespace FSB\Middleware;

class ErrorHandlerMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (\Exception $exception) {
            $view = $this->view($this->viewfile, [
                'code' => $exception->getStatusCode(),
                'message' => $exception->getMessage()
            ]);
            $headers = $exception->getHeaders();
            if (null !== $headers && is_array($headers)) {
                foreach ($headers as $key => $value) {
                    $this->response = $this->response->withHeader($key, $value);
                }
            }
            return $this->response->withStatus($this->exception->getStatusCode());
        }
    }
}
