<?php

namespace app\bark\service;

use app\common\service\BaseService;
use GuzzleHttp\Client;
use think\Exception;

class BarkService extends BaseService
{
    static function pushMsg($title, $body, $url = '', $config = [])
    {
        $device_keys = env('bark.device_keys', '');
        throw_if(empty($device_keys), new Exception('device_keys is empty'));
        $device_keys = array_unique(explode(',', $device_keys));
        $result = [
            'total_amount' => 0,
            'success_amount' => 0,
            'error_amount' => 0,
            'error_msg' => []
        ];
        foreach ($device_keys as $device_key) {
            $res = self::doPushMsg($title, $body, $url, $device_key, $config);
            if (!$res['status']) {
                $result['error_amount']++;
                $result['error_msg'] [] = $res['msg'];
            } else {
                $result['success_amount']++;
            }
            $result['total_amount']++;
        }

        return $result;
    }

    static function doPushMsg($title, $body, $url, $device_key, $config = [])
    {
        $client = new Client([
            'timeout' => 8,
        ]);
        $form = [
            'title' => !empty($title) ? $title : env('bark.title', ''),
            'body' => $body,
            'level' => env('bark.level', 'active'),//推送中断级别。 active：默认值，系统会立即亮屏显示通知
            'url' => $url,
        ];
        env('bark.autoCopy', '') && $form['autoCopy'] = env('bark.autoCopy', '');
        env('bark.copy', '') && $form['copy'] = env('bark.copy', '');
        env('bark.sound', '') && $form['sound'] = env('bark.sound', '');
        env('bark.icon', '') && $form['icon'] = env('bark.icon', '');
        env('bark.group', '') && $form['group'] = env('bark.group', '');
        env('bark.isArchive', '') && $form['isArchive'] = env('bark.isArchive', '');

        // 配置覆盖
        $form = array_merge($form, $config);
        // 处理加密
        $encrypt = env('bark.encrypt_method') === 'aes-256-cbc';
        if ($encrypt) {
            $key = env('bark.encrypt_key', '');
            $iv = env('bark.encrypt_iv', '');
            $plaintext = json_encode($form);
            $ciphertext = openssl_encrypt($plaintext, env('bark.encrypt_method'), $key, OPENSSL_RAW_DATA, $iv);
            $ciphertext = base64_encode($ciphertext);
            $form = [
                'ciphertext' => $ciphertext,
                'iv' => $iv,
            ];
        }
        $host = env('Bark.host', 'https://api.day.app');
        $resp = $client->post($host . '/' . $device_key, [
            'json' => $form,
        ]);
        if ($resp->getStatusCode() !== 200) {
            return self::createReturn(false, null, '请求失败，HTTP 状态码：' . $resp->getStatusCode());
        }
        $body = json_decode((string)$resp->getBody(), true);
        if ($body['code'] !== 200) {
            return self::createReturn(false, null, $body['message']);
        }
        return self::createReturn(true, null, '请求成功');

    }
}
