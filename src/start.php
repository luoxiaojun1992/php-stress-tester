#! /usr/bin/env php
<?php

require_once __DIR__ . '/constants.php';

require_once __DIR__ . '/functions.php';

//环境检查
checkEnvironment();

//获取参数
$options = getOptions($argv, ['c', 'n', 'host', 'uri', 'port', 'ssl', 'step', 'http_method', 'http_body', 'memory_limit']);
$c = $options['c'] ?? 100;
$n = $options['n'] ?? 1000;
$host = $options['host'] ?? 'www.baidu.com';
$uri = $options['uri'] ?? '/';
$port = $options['port'] ?? 443;
$ssl = boolval($options['ssl'] ?? 1);
$step = $options['step'] ?? 10;
$http_method = strtoupper($options['http_method'] ?? HTTP_METHOD_GET);
$http_body = $options['http_body'] ?? '';
$http_body_arr = json_decode($http_body, true);
$memory_limit = $options['memory_limit'] ?? 30000000;


//校验参数
if ($c > MAX_COROUTINE) {
    echo '最大支持2999并发';
    echo PHP_EOL;
    exit(1);
}
if (!is_int($port) && !ctype_digit($port)) {
    echo '端口格式不正确';
    echo PHP_EOL;
    exit(1);
}

//请求时间channel
$executeTime = new chan($n > 0 ? $n : $c * 10);

//并发数
$i = 0;

//统计压测性能
go(function () use ($executeTime, $n, $c, $memory_limit, &$i){
    //Regular
    $minTime = 0;
    $maxTime = 0;
    $totalTime = 0;
    $executedTimes = 0;
    $successTimes = 0;
    $failedTimes = 0;
    $successMinTime = 0;
    $successMaxTime = 0;
    $successTotalTime = 0;
    $failedMinTime = 0;
    $failedMaxTime = 0;
    $failedTotalTime = 0;

    //Qps
    $successTimesPerSecond = 0;
    $qps = -1; //实时QPS,初始值-1
    $avgQps = -1; //平均QPS

    //统计Qps
    swoole_timer_tick(1000, function () use (&$successTimesPerSecond, &$qps, &$avgQps) {
        $qps = $successTimesPerSecond;
        $successTimesPerSecond = 0;

        if ($avgQps <= -1) {
            $avgQps = $qps;
        } else {
            $avgQps = ($avgQps + $qps) / 2;
        }
    });

    while($n > 0 ? $executedTimes < $n : true) {
        $time = $executeTime->pop();
        $result = $time > 0;
        $time = abs($time);
        $totalTime += $time;
        if ($minTime <= 0 || $minTime > $time) {
            $minTime = $time;
        }
        if ($time > $maxTime) {
            $maxTime = $time;
        }
        if ($result) {
            ++$successTimes;
            ++$successTimesPerSecond;
            $successTotalTime += $time;
            if ($successMinTime <= 0 || $successMinTime > $time) {
                $successMinTime = $time;
            }
            if ($time > $successMaxTime) {
                $successMaxTime = $time;
            }
        } else {
            ++$failedTimes;
            $failedTotalTime += $time;
            if ($failedMinTime <= 0 || $failedMinTime > $time) {
                $failedMinTime = $time;
            }
            if ($time > $failedMaxTime) {
                $failedMaxTime = $time;
            }
        }
        ++$executedTimes;

        //内存保护，超过30MB退出
        if (memory_get_usage() >= $memory_limit) {
            break;
        }

        //持续压测,每请求$c次,输出一次性能数据
        if ($n <= 0) {
            if ($executedTimes % $c == 0) {
                output(compact('executedTimes', 'totalTime', 'maxTime', 'minTime', 'successTimes',
                    'successTotalTime', 'successMaxTime', 'successMinTime', 'failedTimes', 'failedTotalTime',
                    'failedMaxTime', 'failedMinTime', 'qps', 'i', 'avgQps'));
            }
        }
    }
    //防止执行太快，定时器来不及计算Qps
    if ($qps <= -1) {
        $qps = $successTimesPerSecond;
        $avgQps = $qps;
    }
    output(compact('executedTimes', 'totalTime', 'maxTime', 'minTime', 'successTimes',
        'successTotalTime', 'successMaxTime', 'successMinTime', 'failedTimes', 'failedTotalTime',
        'failedMaxTime', 'failedMinTime', 'qps', 'i', 'avgQps'));
    exit(0);
});

//发起压测请求,1秒增加一个并发,逐渐加压
$timerId = 0;
$timerId = swoole_timer_tick($step, function () use (&$i, $executeTime, $host, $uri, $port, $ssl, $c, &$timerId, $http_method, $http_body, $http_body_arr) {
    if ($i >= $c) {
        swoole_timer_clear($timerId);
        return;
    }
    go(function () use ($executeTime, $host, $uri, $port, $ssl, $http_method, $http_body, $http_body_arr) {
        $http = new Co\Http\Client($host, $port, $ssl);
        $http->setMethod($http_method);
        if ($http_method != HTTP_METHOD_GET) {
            if (count($http_body_arr) > 0) {
                $http->setData($http_body_arr);
            } elseif ($http_body) {
                $http->setData($http_body);
            }
        }
        while (true) {
            $start = microtime(true);
            $http->execute($uri);
            if ($http->statusCode == 200) {
                $executeTime->push(microtime(true) - $start);
            } else {
                $executeTime->push($start - microtime(true));
            }
        }
    });
    ++$i;
});

echo '测试中...';
echo PHP_EOL;
echo '最大请求并发: ';
echo $c;
echo PHP_EOL;
