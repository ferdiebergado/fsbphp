<?php

namespace App\Command;

class UserShowCommand
{
    public $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}
