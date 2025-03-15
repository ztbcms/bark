## Bark - A push service for iOS

Bark: https://bark.day.app

### Requirement

1. OpenSSL 扩展
```
php -m | grep openssl
```

### Getting Start

1. Copy the `bark/config/sample.env` content into your `.env`
2. change the config in `.env`. The details are in Bark documentation 
  - `device_keys` must be set
  - By default, encrytion is enabled. And only support `aes-256-cbc`.
  - Request `/bark/test/generateKeyAndIv` to get the `key` and `iv`.Set both to your `.env` and Bark iOS App
3. Request `/bark/test/index` to make a push test


### Usage

#### Example 1: 手动调用

最简单的调用方式。

架构图：
```
Developer --- BarkService --> Bark Server ---> Bark iOS App
```

```php
use app\bark\service\BarkService;

BarkService::pushMsg('This is title', 'It works!', 'https://aaa.com', [
    // here is a key-value config that override the config in .env
    'url' => 'https://baidu.com',
    'group' => 'Testing',
])
```

#### Example 2: 自建服务提供（Server-Client）

推送量大，或者跨项目的时候建议采用这种模式，独立成一个Bark 推送服务，性能更好，管理更方便。

架构图：
```
Developer --- BarkApiClientService  ---> [自建API Service] ---> Bark Server ---> Bark iOS App
```

Server API:
```
POST /bark/api/pushMsg

api_key: string required
title: string required
body: string required
```

客户端请求示例

GuzzleHttp 请求示例：
```php
$client = new GuzzleHttp\Client();
$res = $client->request('POST', 'https://Your.domain/bark/api/pushMsg', [
     'form_params' => [
        'api_key' => 'api_key',
        'title' => 'Title',
        'body' => 'Body',
        'url' => ''
    ]
]);
```

【推荐】或直接使用模块提供的客户端实现：
```php
$client = new BarkApiClientService();

$res = $client->pushMsg('标题', 'body内容', [
  'url' => 'http://baidu.com',
  'icon' => 'https://xxx.cn/logo.png',
]);
```

此操作需要在`.env`中配置：
```ini
[bark]
.....
# 作为客户端时，请求服务端推送接口
client_api_push_url=https://your.domian/bark/api/pushMsg
client_api_key=bark3
```

最后启动队列
```bash
# for production
php think queue:listen --queue BarkPushMsg --sleep 3

# for development
php think queue:listen --queue BarkPushMsg --sleep 3 -vvv
```

### FAQ

推送失败？接收不到数据？
> 1、检查 Bark 配置 2、是否存在请求延时过场 3、队列是否启动，查看队列日志