<?php
namespace FSB\Router\Route;

use Aura\Router\Route;

class ModelRoute extends Route
{
    protected $model;

    public function model($model)
    {
        $this->model = $model;
        return $this;
    }
}
