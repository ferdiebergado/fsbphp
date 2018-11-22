<?php

namespace App\View\Template;

interface TemplateInterface
{
    public function render($view, $data);
}
