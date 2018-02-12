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
平均耗时: 183.86986732483毫秒
最大耗时: 216.06707572937毫秒
最小耗时: 149.50203895569毫秒
```

## Arguments
1. Concurrency
2. Hostname
3. Uri
4. Port
5. SSL
