<?php
return [
    $container->get('headers'),
    $container->get('content-type'),
    $container->get('mw_session'),
    $container->get('csrf'),
    $container->get('router'),
    $container->get('auth'),
    $container->get('requesthandler')
];
