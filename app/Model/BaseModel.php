<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as Capsule;
use Dotenv\Dotenv;

class BaseModel extends Model
{
    public function __construct()
    {
        $env = new Dotenv(BASE_PATH);
        $env->load();
        $capsule = new Capsule;
        $config = require(CONFIG_PATH . 'database.php');
        $capsule->addConnection($config);
        $capsule->bootEloquent();
        parent::__construct();
    }
}
