#! /usr/bin/env php
<?php

require_once __DIR__ . '/constants.php';

require_once __DIR__ . '/functions.php';

//环境检查
checkEnvironment();

//获取参数
$c = $argv[1] ?? 100;
$n = $argv[2] ?? 1000;
$host = $argv[3] ?? 'www.baidu.com';
$uri = $argv[4] ?? '/';
$port = $argv[5] ?? 443;
$ssl = boolval($argv[6] ?? 1);
$step = $argv[7] ?? 10;
$memoryLimit = 30000000;

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

$executeTime = new chan($n > 0 ? $n : $c * 10);

//统计压测性能
go(function () use ($executeTime, $n, $c, $memoryLimit){
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
    $qps = 0;

    //统计Qps
    swoole_timer_tick(1000, function () use (&$successTimesPerSecond, &$qps) {
        if ($successTimesPerSecond > 0) {
            if ($qps > 0) {
                $qps = ($successTimesPerSecond + $qps) / 2;
            } else {
                $qps = $successTimesPerSecond;
            }
            $successTimesPerSecond = 0;
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
        if (memory_get_usage() >= $memoryLimit) {
            break;
        }

        //持续压测,每请求$c次,输出一次性能数据
        if ($n <= 0) {
            if ($executedTimes % $c == 0) {
                output(compact('executedTimes', 'totalTime', 'maxTime', 'minTime', 'successTimes',
                    'successTotalTime', 'successMaxTime', 'successMinTime', 'failedTimes', 'failedTotalTime',
                    'failedMaxTime', 'failedMinTime', 'qps'));
            }
        }
    }
    //防止执行太快，定时器来不及计算Qps
    if ($qps <= 0) {
        $qps = $successTimesPerSecond;
    }
    output(compact('executedTimes', 'totalTime', 'maxTime', 'minTime', 'successTimes',
        'successTotalTime', 'successMaxTime', 'successMinTime', 'failedTimes', 'failedTotalTime',
        'failedMaxTime', 'failedMinTime', 'qps'));
    exit(0);
});

//发起压测请求,1秒增加一个并发,逐渐加压
$i = 0;
swoole_timer_tick($step, function () use (&$i, $executeTime, $host, $uri, $port, $ssl, $c) {
    if ($i >= $c) {
        return;
    }
    go(function () use ($executeTime, $host, $uri, $port, $ssl) {
        $http = new Co\Http\Client($host, $port, $ssl);
        while (true) {
            $start = microtime(true);
            $http->get($uri);
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
echo '请求并发: ';
echo $c;
echo PHP_EOL;
