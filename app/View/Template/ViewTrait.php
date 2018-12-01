<?php declare (strict_types = 1);

namespace App\View\Template;

use Zend\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;

trait ViewTrait
{
    protected function view(string $view, $data = []) : ResponseInterface
    {
        $template = $this->template->render($view . ".html.twig", $data);
        return new HtmlResponse($template);
    }
}
