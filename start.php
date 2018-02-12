<?php

require_once __DIR__ . '/functions.php';

//环境检查
checkEnvironment();

//获取参数
$c = $argv[1] ?? 50;
$host = $argv[2] ?? 'api.fourleaver.com';
$uri = $argv[3] ?? '/index/action/index?access-token=test';
$port = $argv[4] ?? 443;
$ssl = boolval($argv[5] ?? 1);

//校验参数
if (!is_int($port) && !ctype_digit($port)) {
    echo '端口格式不正确';
    exit(1);
}

$executeTime = new chan($c);

//统计压测性能
go(function () use ($executeTime, $c){
    $minTime = 0;
    $maxTime = 0;
    $totalTime = 0;
    $executedTimes = 0;
    while($executedTimes < $c) {
        if ($time = $executeTime->pop()) {
            $totalTime += $time;
            if ($minTime <= 0 || $minTime > $time) {
                $minTime = $time;
            }
            if ($time > $maxTime) {
                $maxTime = $time;
            }
            ++$executedTimes;
        }
    }
    echo '请求并发: ';
    echo $executedTimes;
    echo PHP_EOL;
    echo '平均耗时: ';
    echo ($totalTime / $c) * 1000;
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
});

//发起压测请求
for ($i = 0; $i < $c; ++$i) {
    go(function () use ($executeTime, $host, $uri) {
        $start = microtime(true);
        $http = new Co\Http\Client($host, 443, true);
        $ret = $http->get($uri);
        $executeTime->push(microtime(true) - $start);
    });
}
echo '测试中...' . PHP_EOL;
