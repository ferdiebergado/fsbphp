<?php

$builder = new DI\ContainerBuilder();
if (!DEBUG_MODE) {
    $builder->enableCompilation(CACHE_PATH . 'container');
}
$builder->useAutowiring(false);
$builder->useAnnotations(false);
$dependencies = require(CONFIG_PATH . 'dependencies.php');
$builder->addDefinitions($dependencies);
$container = $builder->build();

return $container;
