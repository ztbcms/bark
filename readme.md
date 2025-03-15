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


#### Example 1: Used by manually



```php
use app\bark\service\BarkService;

BarkService::pushMsg('This is title', 'It works!', 'https://aaa.com', [
    // here is a key-value config that override the config in .env
    'url' => 'https://baidu.com',
    'group' => 'Testing',
])
```

#### Example 2: As a api service

For the big project, mutil projects, or imporve the network performance. You can use the api service.

API:
```
POST /bark/api/pushMsg

title: string required
body: string required
url: string optional
```

GuzzleHttp 请求示例：
```php
$client = new GuzzleHttp\Client();
$res = $client->request('POST', 'https://Your.domain/bark/api/pushMsg', [
     'form_params' => [
        'title' => 'Title',
        'body' => 'Body',
        'url' => ''
    ]
]);
```

启动队列
```bash
# for production
php think queue:listen --queue BarkPushMsg --sleep 3

# for development
php think queue:listen --queue BarkPushMsg --sleep 3 -vvv
```

### FAQ

推送失败？接收不到数据？
> 1、检查 Bark 配置 2、是否存在请求延时过场 3、队列是否启动，查看队列日志