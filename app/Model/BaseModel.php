<?php

namespace App\Model;

use FSB\Container;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    public function __construct()
    {
        parent::__construct();
        $container = new Container();
        $env = $container->get('env');
        $env->load();
        $config = $container->get('config');
        $capsule = $container->get('capsule');
        $capsule->addConnection($config->get('database'));
        $capsule->bootEloquent();
    }
}
