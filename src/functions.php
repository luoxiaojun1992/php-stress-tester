<?php

if (!function_exists('checkEnvironment')) {
    function checkEnvironment()
    {
        if (php_sapi_name() != 'cli') {
            echo '请在cli模式下运行本工具';
            echo PHP_EOL;
            exit(1);
        }
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

if (!function_exists('getOptions')) {
    function getOptions($args, array $option_names)
    {
        $options = [];
        foreach ($args as $k => $arg) {
            if (strpos($arg, '-') === 0) {
                if (strpos($arg, '--') === 0) {
                    $option_value = substr($arg, 2);
                    list($option_name, $option_value) = explode('=', $option_value);
                    if (in_array($option_name, $option_names)) {
                        $options[$option_name] = $option_value;
                    }
                } else {
                    $option_name = substr($arg, 1);
                    if (in_array($option_name, $option_names)) {
                        $options[$option_name] = $args[$k + 1];
                    }
                }
            }
        }
        return $options;
    }
}

if (!function_exists('output')) {
    function output($params)
    {
        //Clear stdout
        system('clear');

        $executedTimes = $params['executedTimes'] ?? 0;
        $totalTime = $params['totalTime'] ?? 0;
        $maxTime = $params['maxTime'] ?? 0;
        $minTime = $params['minTime'] ?? 0;
        $successTimes = $params['successTimes'] ?? 0;
        $successTotalTime = $params['successTotalTime'] ?? 0;
        $successMaxTime = $params['successMaxTime'] ?? 0;
        $successMinTime = $params['successMinTime'] ?? 0;
        $failedTimes = $params['failedTimes'] ?? 0;
        $failedTotalTime = $params['failedTotalTime'] ?? 0;
        $failedMaxTime = $params['failedMaxTime'] ?? 0;
        $failedMinTime = $params['failedMinTime'] ?? 0;
        $qps = $params['qps'] ?? 0;
        $i = $params['i'] ?? 0;
        $avgQps = $params['avgQps'] ?? 0;

        echo '请求并发: ';
        echo $i;
        echo PHP_EOL;
        echo '请求总数: ';
        echo $executedTimes;
        echo PHP_EOL;
        echo '平均耗时: ';
        echo $executedTimes > 0 ? ($totalTime / $executedTimes) * 1000 : 0;
        echo '毫秒';
        echo PHP_EOL;
        echo '最大耗时: ';
        echo $maxTime * 1000;
        echo '毫秒';
        echo PHP_EOL;
        echo '最小耗时: ';
        echo $minTime * 1000;
        echo '毫秒';
        echo PHP_EOL;
        echo '成功请求总数: ';
        echo $successTimes;
        echo PHP_EOL;
        echo '成功率: ';
        echo $executedTimes > 0 ? ($successTimes / $executedTimes) * 100 : 0;
        echo '%';
        echo PHP_EOL;
        echo '成功平均耗时: ';
        echo $successTimes > 0 ? ($successTotalTime / $successTimes) * 1000 : 0;
        echo '毫秒';
        echo PHP_EOL;
        echo '成功最大耗时: ';
        echo $successMaxTime * 1000;
        echo '毫秒';
        echo PHP_EOL;
        echo '成功最小耗时: ';
        echo $successMinTime * 1000;
        echo '毫秒';
        echo PHP_EOL;
        echo '失败请求总数: ';
        echo $failedTimes;
        echo PHP_EOL;
        echo '失败率: ';
        echo $executedTimes > 0 ? ($failedTimes / $executedTimes) * 100 : 0;
        echo '%';
        echo PHP_EOL;
        echo '失败平均耗时: ';
        echo $failedTimes > 0 ? ($failedTotalTime / $failedTimes) * 1000 : 0;
        echo '毫秒';
        echo PHP_EOL;
        echo '失败最大耗时: ';
        echo $failedMaxTime * 1000;
        echo '毫秒';
        echo PHP_EOL;
        echo '失败最小耗时: ';
        echo $failedMinTime * 1000;
        echo '毫秒';
        echo PHP_EOL;
        echo '实时QPS: ';
        echo $qps;
        echo PHP_EOL;
        echo '平均QPS: ';
        echo $avgQps;
        echo PHP_EOL;
        echo '内存占用: ';
        echo memory_get_usage() / 1000;
        echo 'KB';
        echo PHP_EOL;
        echo PHP_EOL;
        echo PHP_EOL;
    }
}
