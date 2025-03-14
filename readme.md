## Bark - A push service for iOS

Bark: https://bark.day.app

### Getting Start

1. Copy the `bark/config/sample.env` content into your `.env`
2. change the config in `.env`. The details are in Bark documentation 
  - `device_keys` must be set
  - By default, encrytion is enabled. And only support `aes-256-cbc`.
  - Request `/bark/test/generateKeyAndIv` to get the `key` and `iv`.Set both to your `.env` and Bark iOS App
3. Request `/bark/test/index` to make a push test
4. Example:
```php
use app\bark\service\BarkService;

BarkService::pushMsg('This is title', 'It works!', 'https://baidu.com', [
    'url' => 'https://devonline.net/'
])
```

### Requirement

1. OpenSSL 扩展
```
php -m | grep openssl
```

### FAQ

推送失败？接收不到数据？
> 1、检查配置 2、是否存在请求延时过场