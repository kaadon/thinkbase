<?php

// +----------------------------------------------------------------------
// | Project: KaadonThinkBase By PhpStorm
// +----------------------------------------------------------------------
// | Author : kaadon.com <kaadon.com@gmail.com>
// +----------------------------------------------------------------------
// | Date   : 2023/11/15 11:15 AM
// +----------------------------------------------------------------------
// | WebUrl : http://developer.kaadon.com
// +----------------------------------------------------------------------
// | Telgram: @kaadon
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://kaadon.com All rights reserved.
// +----------------------------------------------------------------------


namespace Kaadon\ThinkBase\services;

/**
 *
 */
class HttpRequest
{
    /**
     * @var mixed|string
     */
    private mixed $userAgent;
    /**
     * @var mixed|string
     */
    private mixed $ipAddress;

    // 构造函数，可以设置默认的User-Agent和IP地址

    /**
     * @param $userAgent
     * @param $ipAddress
     */
    public function __construct($userAgent = '', $ipAddress = '')
    {
        $this->userAgent = $userAgent;
        $this->ipAddress = $ipAddress;
    }

    // 设置或更改User-Agent

    /**
     * @param $userAgent
     * @return void
     */
    public function setUserAgent($userAgent): void
    {
        $this->userAgent = $userAgent;
    }

    // 设置或更改IP地址

    /**
     * @param $ipAddress
     * @return void
     */
    public function setIpAddress($ipAddress): void
    {
        $this->ipAddress = $ipAddress;
    }
    // 发送GET请求的方法

    /**
     * @param $url
     * @param $headers
     * @return bool|string|null
     */
    public function sendGet($url, $headers = []): bool|string|null
    {
        return $this->sendRequest($url, 'GET', null, $headers);
    }

    // 发送POST请求的方法

    /**
     * @param $url
     * @param $data
     * @param $headers
     * @return bool|string|null
     */
    public function sendPost($url, $data, $headers = []): bool|string|null
    {
        return $this->sendRequest($url, 'POST', $data, $headers);
    }

    // 发送PUT请求的方法

    /**
     * @param $url
     * @param $data
     * @param $headers
     * @return bool|string|null
     */
    public function sendPut($url, $data, $headers = []): bool|string|null
    {
        return $this->sendRequest($url, 'PUT', $data, $headers);
    }

    // 发送DELETE请求的方法

    /**
     * @param $url
     * @param $headers
     * @return bool|string|null
     */
    public function sendDelete($url, $headers = []): bool|string|null
    {
        return $this->sendRequest($url, 'DELETE', null, $headers);
    }

    // 通用请求方法

    /**
     * @param $url
     * @param $method
     * @param $data
     * @param $headers
     * @return bool|string|null
     */
    private function sendRequest($url, $method, $data = null, $headers = []): bool|string|null
    {
        $ch = curl_init();

        // 设置cURL选项
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // 设置请求头
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        // 模拟IP地址
        if (!empty($this->ipAddress)) {
            $headers[] = "REMOTE_ADDR: $this->ipAddress";
            $headers[] = "HTTP_X_FORWARDED_FOR: $this->ipAddress";
        }

        // 模拟User-Agent
        if (!empty($this->userAgent)) {
            curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        } else {
            // 默认为手机User-Agent
            $this->userAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 10_3 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) CriOS/56.0.2924.75 Mobile/14E5239e Safari/602.1';
            curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        }

        // 设置请求方法
        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
        }

        // 执行cURL会话
        $response = curl_exec($ch);

        // 检查是否有错误发生
        if (curl_errno($ch)) {
            curl_close($ch);
            return null;
        }

        // 关闭cURL会话
        curl_close($ch);

        // 返回获取的数据
        return $response;
    }
}
