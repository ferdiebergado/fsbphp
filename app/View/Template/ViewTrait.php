<?php

namespace App\View\Template;

trait ViewTrait
{
    protected function view($view, $data = [])
    {
        $template = $this->template->render($view . ".html.twig", $data);
        $this->response->getBody()->write($template);
        return $this->response;
    }
}
