<?php

namespace app\bark\job;

use app\bark\service\BarkService;
use app\common\libs\queue\BaseQueueJob;
use think\facade\Log;
use think\queue\Job;

class PushMsgJob extends BaseQueueJob
{
    // 队列名称
    public const QUEUE_NAME = 'BarkPushMsg';
    // 最大重试次数
    private const MAX_ATTEMPTS = 3;
    // 重试延迟时间（秒）
    private const BASE_DELAY = 5;

    /**
     * 处理 Bark 消息推送任务
     * @param Job $job 队列任务
     * @param array $data 任务数据
     */
    public function fire(Job $job, $data = [])
    {
        try {
            // 获取推送参数
            $title = $data['title'] ?? '';
            $body = $data['body'] ?? '';
            $url = $data['url'] ?? '';
            $config = $data['config'] ?? [];

            if (empty($title) || empty($body)) {
                throw new \Exception('推送参数异常');
            }

            // 执行推送
            $res = BarkService::pushMsg($title, $body, $url, $config);

            if ($res['success_amount'] === $res['total_amount']) {
                // 推送成功，删除任务
                $job->delete();
                return;
            }

            // 推送失败，记录日志
            Log::error('Bark推送失败', [
                'error' => $res['error_msg'],
                'data' => $data
            ]);

            // 重试
            $this->retry($job);
        } catch (\Exception $e) {
            Log::error('Bark推送任务异常', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            // 重试
            $this->retry($job);
        }
    }

    private function retry(Job $job)
    {
        // 重试次数超过x次则删除任务
        if ($job->attempts() > self::MAX_ATTEMPTS) {
            $job->delete();
            return;
        }

        // 延迟x秒后重试
        $job->release(self::BASE_DELAY);
    }

    /**
     * 任务执行失败的回调
     * @param array $data 任务数据
     */
    public function failed($data)
    {
        Log::error('Bark推送任务最终失败', [
            'data' => $data
        ]);
    }
}
