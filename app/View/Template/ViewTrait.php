<?php

namespace App\View\Template;

use Zend\Diactoros\Response\HtmlResponse;

trait ViewTrait
{
    protected function view($view, $data = [])
    {
        $template = $this->template->render($view . ".html.twig", $data);
        // $this->response->getBody()->write($template);
        return new HtmlResponse($template);
        // return $this->response;
    }
}
