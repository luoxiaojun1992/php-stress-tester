# php-stress-tester

## Description
A simple stress tester based on swoole coroutine.

## Requirements
1. Swoole2.1.0+
2. PHP7.0+

## Usage
```shell
php start.php 100 www.baidu.com / 443 1
```
Output
```shell
测试中...
请求并发: 100
平均耗时: 192.02973604202毫秒
```

## Arguments
1. Concurrency
2. Hostname
3. Uri
4. Port
5. SSL
