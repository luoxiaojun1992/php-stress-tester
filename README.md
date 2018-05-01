# php-stress-tester

## Description
A simple stress tester based on swoole coroutine.

## Requirements
1. Swoole2.1.0+ (编译时请添加参数 --enable-openssl --enable-coroutine)
2. PHP7.0+
3. 勿同时安装opencensus扩展，经测试有内存泄漏问题

## Usage
```shell
php start.php 100 1000 www.baidu.com / 443 1 1
```
Output
```shell
测试中...
最大请求并发: 100
请求并发: 100
请求总数: 1000
平均耗时: 43.356620550156毫秒
最大耗时: 174.54195022583毫秒
最小耗时: 26.26895904541毫秒
成功请求总数: 1000
成功率: 100%
成功平均耗时: 43.356620550156毫秒
成功最大耗时: 174.54195022583毫秒
成功最小耗时: 26.26895904541毫秒
失败请求总数: 0
失败率: 0%
失败平均耗时: 0毫秒
失败最大耗时: 0毫秒
失败最小耗时: 0毫秒
QPS: 1000
内存占用: 2012.232KB
```

## Arguments
1. Concurrency
2. Requests
3. Hostname
4. Uri
5. Port
6. SSL
7. Concurrency Step
8. Memory Limit
