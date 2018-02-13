# php-stress-tester

## Description
A simple stress tester based on swoole coroutine.

## Requirements
1. Swoole2.1.0+
2. PHP7.0+

## Usage
```shell
php start.php 100 1000 www.baidu.com / 443 1
```
Output
```shell
测试中...
请求并发: 100
请求总数: 1000
平均耗时: 45.958206415176毫秒
最大耗时: 217.64087677002毫秒
最小耗时: 25.42519569397毫秒
```

## Arguments
1. Concurrency
2. Requests
3. Hostname
4. Uri
5. Port
6. SSL
