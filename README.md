# php-stress-tester

## Description
A simple stress tester based on swoole coroutine.

## Requirements
1. Swoole2.1.0+ (编译时请添加参数 --enable-openssl --enable-coroutine)
2. PHP7.0+

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
平均耗时: 41.176548957825毫秒
最大耗时: 156.91804885864毫秒
最小耗时: 25.597810745239毫秒
成功请求总数: 1000
成功平均耗时: 41.176548957825毫秒
成功最大耗时: 156.91804885864毫秒
成功最小耗时: 25.597810745239毫秒
失败请求总数: 0
失败平均耗时: 0毫秒
失败最大耗时: 0毫秒
失败最小耗时: 0毫秒
平均QPS: 1000
内存占用: 2065.68KB
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
