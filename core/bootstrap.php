<?php

defined('BASE_DIR') or define('BASE_DIR', __DIR__ . '/..');

spl_autoload_register(function($className) {
    $namespaces = explode("\\", $className);
    $className = array_pop($namespaces);
    $dir = BASE_DIR;
    foreach ($namespaces as $dirName) {
        $dir .= '/' . $dirName;
        if (!is_dir($dir)) {
            return false;
        }
    }
    if (!is_file($dir . '/' . $className . '.php')) {
        return false;
    }
    include $dir . '/' . $className . '.php';
});