<?php

namespace App\View\Template\Twig;

use App\View\Template\TemplateInterface;

class TwigTemplate implements TemplateInterface
{
    protected $template;

    public function __construct(\Twig_Environment $template)
    {
        $this->template = $template;
    }

    public function render($view, $data)
    {
        return $this->template->render($view, $data);
    }

    public function addGlobal($name, $value)
    {
        return $this->template->addGlobal($name, $value);
    }
}
