<?php

namespace Bergado\Presentation\Web\Pub\Template\Twig;

use Bergado\Core\Application\Service\TemplateInterface;

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
}
