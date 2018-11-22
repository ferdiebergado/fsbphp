<?php
return [
    ['GET', '/', ['HomeController', 'index']],
    ['GET', '/hello/{name}', ['HomeController', 'hello']],
    ['GET', '/login', ['LoginController', 'show']],
    ['POST', '/login', ['LoginController', 'login']],
    ['POST', '/logout', ['LoginController', 'logout']],
    ['GET', '/users/{user}', ['UserController', 'show']]
];
