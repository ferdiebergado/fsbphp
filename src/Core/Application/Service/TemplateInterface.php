<?php

namespace Bergado\Core\Application\Service;

interface TemplateInterface
{
    public function render($view, $data);
}
