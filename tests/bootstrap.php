<?php

$basePath = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);

set_include_path(implode(PATH_SEPARATOR, array(
    get_include_path(),
    $basePath . DIRECTORY_SEPARATOR . 'src',
)));

require $basePath . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

spl_autoload_register(function ($class) {
    $classPath = str_replace('_', DIRECTORY_SEPARATOR, $class);
    include_once $classPath . '.php';
});
