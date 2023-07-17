<?php
/**
 * Created by   : PhpStorm
 * Project      : KaadonThinkBase
 * Web          : https://developer.kaadon.com
 * User         : kaaddon.com
 * Date         : 2023/7/1 4:30 PM
 **/

/** REDIS **/

use Kaadon\ThinkBase\services\redisService;
use think\Model;
use think\paginator\driver\Bootstrap;
use think\Response;
use think\response\Json;

if (!function_exists('redisCacheSet')) {

    /**
     * 设置缓存
     *
     * @param string $name
     * @param        $value
     * @param int $expire
     * @param int $select
     *
     * @return bool
     */
    function redisCacheSet(string $name, $value, int $expire = 3600, int $select = 1): bool
    {
        try {
            $redis = redisService::instance($select);
            $redis->set('cache:' . $name, json_encode($value));
            $redis->expire('cache:' . $name, $expire);
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }
}
if (!function_exists('redisCacheGet')) {
    /**
     * 获取缓存
     *
     * @param string $name //key
     * @param int $select //redis库
     *
     * @return array|bool|int
     * @throws RedisException
     */
    function redisCacheGet(string $name, int $select = 1): array|bool|int
    {
        $resultData = [];
        $redis      = redisService::instance($select);
        $data       = $redis->get('cache:' . $name);
        if (!empty($data)) {
            $resultData = json_decode($data, true);
        }
        return $resultData;
    }
}
if (!function_exists('redisCacheDel')) {

    /**
     * 删除缓存
     *
     * @param string $name
     * @param int $select
     *
     * @return Redis|int|bool
     * @throws RedisException
     */
    function redisCacheDel(string $name, int $select = 1): Redis|int|bool
    {
        return redisService::instance($select)
                           ->del("cache:" . $name);
    }
}

if (!function_exists('paginate')) {
    /**
     * 数据分页处理
     * @param object|array $data
     * @return Json
     */
    function paginate(object|array $paginateData, array $param = []): Json
    {
        if ($paginateData instanceof Bootstrap) $paginateData = $paginateData->toArray();
        list("current_page" => $current_page,
            "per_page" => $per_page,
            "last_page" => $last_page,
            "data" => $list,
            "total" => $total)
            = $paginateData;

        $param = array_merge($param, [
            "page" => $current_page,
            "pages" => $last_page,
            "list" => $list,
            "limit" => $per_page,
            "count" => $total
        ]);
        return success($param);
    }
}

if (!function_exists('is_debug')) {
    /**
     * 是否为DEBUG
     *
     * @return bool|null
     */
    function is_debug(): ?bool
    {
        return \think\facade\Env::get("app_debug") ?? false;
    }
}


if (!function_exists('is_dev')) {
    /**
     * 是否为DEV开发模式
     *
     * @return bool|null
     */
    function is_dev(): ?bool
    {
        return \think\facade\Env::get("app_dev") ?? false;
    }
}


/** RETURN **/
if (!function_exists('success')) {
    /**
     * @param array $data //数据
     * @param string $msg //语言
     * @param int $statusCode 错误码
     *
     * @return Json
     */
    function success(object|array|string|null $data = null, string $msg = "success", int $statusCode = 200): Json
    {
        if ($data instanceof Model) $data = $data->toArray();
        $message = '';
        if (strpos($msg, "::") !== false) {
            $msgArr = explode('::', $msg);
            foreach ($msgArr as $item) {
                if (!empty($item)) {
                    $message .= lang($item);
                }
            }
        } else {
            if (empty($msg)) $message = lang($msg);
        }
        $resultData = [
            'code'    => $statusCode,
            'message' => $message,
            'data'    => $data,
            'time'    => time()
        ];

        return json($resultData);
    }
}
if (!function_exists('successes')) {
    /**
     * @param array $data //数据
     * @param string $msg //语言
     * @param int $statusCode 错误码
     *
     * @return Json
     */
    function successes(string $msg = "success", object|array|string|null $data = null, int $statusCode = 200): Json
    {
        if ($data instanceof Model) $data = $data->toArray();
        $message = '';
        if (strpos($msg, "::") !== false) {
            $msgArr = explode('::', $msg);
            foreach ($msgArr as $item) {
                if (!empty($item)) {
                    $message .= lang($item);
                }
            }
        } else {
            if (empty($msg)) $message = lang($msg);
        }
        $resultData = [
            'code'    => $statusCode,
            'message' => $message,
            'data'    => $data,
            'time'    => time()
        ];

        return json($resultData);
    }
}
if (!function_exists('error')) {
    /**
     * @param string $msg //语言
     * @param int $statusCode //错误码
     * @param array $err //错误内容
     *
     * @return Json
     */
    function error(string $msg = 'error', int $statusCode = 201, array $err = []): Json
    {
        $message = '';
        if (strpos($msg, "::") !== false) {
            $msgArr = explode('::', $msg);
            foreach ($msgArr as $item) {
                if (!empty($item)) {
                    $message .= lang($item);
                }
            }
        } else {
            if (empty($msg)) $message = lang($msg);
        }
        $resultData = [
            'code'    => $statusCode,
            'message' => $message,
            'error'   => $err,
            'time'    => time()
        ];
        return json($resultData);
    }
}


