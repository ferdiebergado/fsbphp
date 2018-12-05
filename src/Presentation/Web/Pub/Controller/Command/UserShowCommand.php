<?php

namespace Bergado\Presentation\Web\Pub\Controller\Command;

class UserShowCommand
{
    public $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}
