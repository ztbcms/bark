<?php

namespace app\bark\controller;

use app\bark\service\BarkService;
use app\BaseController;
use app\Request;
use think\facade\Log;

class Api extends BaseController
{
	// 推送消息
    public function push(Request $request)
    {
        if (!$this->validateApiKey()) {
            return self::returnErrorJson('API key is invalid');
        }
        $title = input('title');
        $body = input('body');
        $url = input('url');
        if (empty($title) || empty($body)) {
            return self::returnErrorJson('title and body are required');
        }
        $config = [];
        $config_keys = ['level', 'autoCopy', 'copy', 'sound', 'icon', 'group', 'isArchive'];
        foreach ($config_keys as $key) {
            if (!empty(input('post.' . $key))) {
                $config[$key] = input('post.' . $key);
            }
        }
        try {
            $res = BarkService::pushMsg($title, $body, $url, $config);
            return self::returnSuccessJson($res);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return self::returnErrorJson($e->getMessage());
        }
    }

	// 验证 API key
    private function validateApiKey()
    {
        $api_keys = env('bark.api_keys', '');
        $api_key = input('post.api_key');
        return !empty($api_key) && strpos($api_keys, $api_key) !== false;
    }
}
