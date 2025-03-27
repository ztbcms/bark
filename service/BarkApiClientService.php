<?php

namespace app\bark\service;

// Bark API 客户端
class BarkApiClientService
{
    private string $client_api_push_url;
    private string $client_api_key;

    public function __construct(array $config = [])
    {
        $this->client_api_push_url = $config['client_api_push_url'] ?? env('bark.client_api_push_url');
        $this->client_api_key = $config['client_api_key'] ?? env('bark.client_api_key');

        if (empty($this->client_api_push_url) || empty($this->client_api_key)) {
            throw new \InvalidArgumentException('Bark API 配置缺失');
        }
    }
    /**
     * 发起推送
     * @param string $title 标题
     * @param string $body 内容
     * @param string $url 链接
     * @param string $device_key 设备key
     * @param array $config 配置
     * @return array
     */
    public function pushMsg(string $title, string $body, array $options = []): array
    {
        $client = new \GuzzleHttp\Client([
            'timeout' => 8,
        ]);
        $form_params =  array_merge([
          'api_key' => $this->client_api_key,
          'title' => $title,
          'body' => $body,
        ], $options);

        $res = $client->request('POST', $this->client_api_push_url, [
             'form_params' => $form_params
        ]);
        return json_decode($res->getBody()->getContents(), true);
    }
}
