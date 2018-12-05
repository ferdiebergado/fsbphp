<?php

namespace Bergado\Domain\Entity;

use Bergado\Domain\Entity\BaseModel;

class User extends BaseModel
{
    protected $fillable = [
        'email',
        'password',
        'last_login',
        'ipv4',
        'ipv6',
        'user_agent',
        'role',
    ];

    protected $hidden = [
        'password'
    ];
}
