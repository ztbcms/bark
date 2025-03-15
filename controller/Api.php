<?php

namespace app\bark\controller;

use app\bark\job\PushMsgJob;
use app\BaseController;
use app\Request;
use think\facade\Log;
use think\facade\Queue;

class Api extends BaseController
{
    // 推送消息
    public function pushMsg(Request $request)
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
            // 使用队列推送
            $queue_data = ['title' => $title,'body' => $body, 'url' => $url,'config' => $config];
            Queue::push(PushMsgJob::class, $queue_data, PushMsgJob::QUEUE_NAME);
            return self::returnSuccessJson([], '已经添加到推送队列');
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
