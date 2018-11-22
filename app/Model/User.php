<?php

namespace App\Model;

use App\Model\BaseModel;

class User extends BaseModel
{
    protected $fillable = [
        'email',
        'password',
        'last_login',
        'role'
    ];

    protected $hidden = [
        'password'
    ];
}
