<?php

if (!function_exists('checkEnvironment')) {
    function checkEnvironment()
    {
        if (!extension_loaded('swoole')) {
            echo '请安装Swoole2.1.0+';
            echo PHP_EOL;
            exit(1);
        }
        if (!function_exists('go')) {
            echo '请安装Swoole2.1.0+';
            echo PHP_EOL;
            exit(1);
        }
        if (!class_exists('chan')) {
            echo '请安装Swoole2.1.0+';
            echo PHP_EOL;
            exit(1);
        }
        if (!class_exists('Co\Http\Client')) {
            echo '请安装Swoole2.1.0+';
            echo PHP_EOL;
            exit(1);
        }
    }
}
