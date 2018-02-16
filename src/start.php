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

$executeTime = new chan($n);
$result = new chan($n);

//统计压测性能
go(function () use ($executeTime, $result, $n){
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
    while($executedTimes < $n) {
        $time = $executeTime->pop();
        $totalTime += $time;
        if ($minTime <= 0 || $minTime > $time) {
            $minTime = $time;
        }
        if ($time > $maxTime) {
            $maxTime = $time;
        }
        if ($result->pop()) {
            ++$successTimes;
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
    }
    echo '请求总数: ';
    echo $executedTimes;
    echo PHP_EOL;
    echo '平均耗时: ';
    echo ($totalTime / $executedTimes) * 1000;
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
    echo '成功平均耗时: ';
    echo ($successTotalTime / $successTimes) * 1000;
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
    echo '失败平均耗时: ';
    echo ($failedTotalTime / $failedTimes) * 1000;
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
    exit(0);
});

//发起压测请求
for ($i = 0; $i < $c; ++$i) {
    go(function () use ($executeTime, $result, $host, $uri) {
        $http = new Co\Http\Client($host, 443, true);
        while (true) {
            $start = microtime(true);
            $http->get($uri);
            $result->push($http->statusCode == 200);
            $executeTime->push(microtime(true) - $start);
        }
    });
}
echo '测试中...';
echo PHP_EOL;
echo '请求并发: ';
echo $c;
echo PHP_EOL;
