<?php

set_include_path(implode(PATH_SEPARATOR, array(
    get_include_path(),
    realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src'),
)));

spl_autoload_register(function ($class) {
    $classPath = str_replace('_', DIRECTORY_SEPARATOR, $class);
    include_once $classPath . '.php';
});
