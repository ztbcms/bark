<?php

namespace app\bark\controller;

use app\admin\service\AdminUserService;
use app\bark\service\BarkService;
use app\common\controller\AdminController;

class Test extends AdminController
{
    function index()
    {
        if (!AdminUserService::getInstance()->isAdministrator()) {
            return self::returnErrorJson('暂无权限');
        }

        return self::returnSuccessJson(BarkService::pushMsg('This is title', 'It works!', 'https://baidu.com', [
            'url' => 'https://devonline.net/'
        ]));
    }

    // 生成key、 iv
    function generateKeyAndIv()
    {
        $key = md5(time());
        $iv = md5($key);

        // 检查自定义密钥和 IV 的长度，并进行调整
        $key = substr($key, 0, 32); // 确保密钥长度为32字节
        $iv = substr($iv, 0, 16);   // 确保 IV 长度为16字节

        // 如果密钥或 IV 长度不足，使用哈希函数扩展它们到所需长度
        if (strlen($key) < 32) {
            $key = substr(hash('sha256', $key), 0, 32);
        }
        if (strlen($iv) < 16) {
            $iv = substr(hash('sha256', $iv), 0, 16);
        }

        return self::returnSuccessJson([
            'key' => $key,
            'iv' => $iv,
        ]);
    }
}