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
use think\facade\Env;
use think\Model;
use think\paginator\driver\Bootstrap;
use think\response\Json;

/** redis 缓存 **/
if (!function_exists('redisCacheSet')) {

    /**
     * 设置缓存
     *
     * @param string $name
     * @param        $value
     * @param int $expire
     * @return bool
     */
    function redisCacheSet(string $name, $value, int $expire = 3600): bool
    {
        try {
            $redis = redisService::instance();
            $redis->set('cache:' . $name, json_encode($value));
            $redis->expire('cache:' . $name, $expire);
        } catch (\Exception) {
            return false;
        }
        return true;
    }
}
if (!function_exists('redisCacheGet')) {

    /**
     *  获取缓存
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    function redisCacheGet(string $name): mixed
    {
        try {
            //逻辑代码
            $resultData = [];
            $redis = redisService::instance();
            $data = $redis->get('cache:' . $name);
            if (!empty($data)) {
                $resultData = json_decode($data, true);
            }
            return $resultData;
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
}
if (!function_exists('redisCacheDel')) {

    /**
     * 删除缓存
     *
     * @param string $name
     * @return Redis|int|bool
     * @throws Exception
     */
    function redisCacheDel(string $name): Redis|int|bool
    {
        try {
            //逻辑代码
            $redis = redisService::instance();
            return $redis->del("cache:" . $name);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
}
if (!function_exists('redisCacheUnlink')) {

    /**
     * 删除缓存
     *
     * @param string $name
     * @return Redis|int|bool
     * @throws Exception
     */
    function redisCacheUnlink(string $name): Redis|int|bool
    {
        try {
            //逻辑代码
            $redis = redisService::instance();
            return $redis->unlink("cache:" . $name);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
}
if (!function_exists('redisCacheDelAll')) {

    /**
     * 批量删除缓存
     *
     * @param string $name
     * @param int|null $select
     *
     * @return Redis|int|bool
     * @throws Exception
     */
    function redisCacheDelAll(string $name, ?int $select): Redis|int|bool
    {
        try {
            //逻辑代码
            $redis = redisService::instance();
            $keys = $redis->keys("cache:" . $name);
            foreach ($keys as $key) {
                $redis->del($key);
            }
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
        return true;
    }
}

/** 分页 **/
if (!function_exists('paginate')) {
    /**
     * 数据分页处理
     *
     * @param object|array $paginateData
     * @param array $param
     *
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

/** 环境判断 **/
if (!function_exists('is_debug')) {
    /**
     * 是否为DEBUG
     *
     * @return bool|null
     */
    function is_debug(): ?bool
    {
        return Env::get("app_debug") ?? false;
    }
}
if (!function_exists('is_dev')) {
    /**
     * 是否为DEV开发分支
     *
     * @return bool|null
     */
    function is_dev(): ?bool
    {
        return Env::get("app_dev") ?? false;
    }
}
if (!function_exists('is_demo')) {
    /**
     * 是否为演示模式
     *
     * @return bool|null
     */
    function is_demo(): ?bool
    {
        return Env::get("app_demo") ?? false;
    }
}

/** yaconf **/
if (!function_exists("get_conf")) {
    /**
     * 获取配置
     *
     * @param string $group
     * @param string|null $name
     * @param array|string|int|float|bool $default
     * @param string $filePath
     *
     * @return array|string|int|float|bool
     */
    function get_conf(string $group, ?string $name = null, array|string|int|float|bool $default = "", string $filePath = "app",): array|string|int|float|bool
    {
        $envPath = "{$group}";
        if (!is_null($name)) $envPath .= $name;
        return get_yaconf_config($group, $name, $filePath) ?? (Env::get($envPath) ?: $default);
    }
}


/** RETURN **/
if (!function_exists('success_zip')) {
    /**
     * 压缩返回
     *
     * @param object|array|string|null $data //数据
     * @param string $msg //语言
     * @param int $statusCode 错误码
     *
     * @return Json
     */
    function success_zip(object|array|string|null $data = null, string $msg = "success", int $statusCode = 200): Json
    {
        if ($data instanceof Model) $data = $data->toArray();
        $message = getMessageStr($msg);
        $data = base64_encode(string: gzcompress(json_encode($data)));
        $resultData = [
            'code' => $statusCode,
            'message' => empty($message) ? $msg : $message,
            'data' => $data,
            'time' => time()
        ];

        return json($resultData);
    }
}
if (!function_exists('success')) {
    /**
     * @param object|array|string|null $data //数据
     * @param string $msg //语言
     * @param int $statusCode 错误码
     *
     * @return Json
     */
    function success(object|array|string|null $data = null, string $msg = "success", int $statusCode = 200): Json
    {
        if ($data instanceof Model) $data = $data->toArray();
        $message = getMessageStr($msg);
        $resultData = [
            'code' => $statusCode,
            'message' => empty($message) ? $msg : $message,
            'data' => $data,
            'time' => time()
        ];

        return json($resultData);
    }
}
if (!function_exists('successes')) {
    /**
     * @param string $msg //语言
     * @param object|array|string|null $data //数据
     * @param int $statusCode 错误码
     *
     * @return Json
     */
    function successes(string $msg = "success", object|array|string|null $data = null, int $statusCode = 200): Json
    {
        if ($data instanceof Model) $data = $data->toArray();
        $message = getMessageStr($msg);
        $resultData = [
            'code' => $statusCode,
            'message' => empty($message) ? $msg : $message,
            'data' => $data,
            'time' => time()
        ];

        return json($resultData);
    }
}
if (!function_exists('error')) {
    /**
     * @param string $msg //语言
     * @param int $statusCode //错误码
     * @param array|string $err //错误内容
     *
     * @return Json
     */
    function error(string $msg = 'error', int $statusCode = 201, array|string $err = []): Json
    {
        $message = getMessageStr($msg);
        $resultData = [
            'code' => $statusCode,
            'message' => empty($message) ? $msg : $message,
            'error' => $err,
            'time' => time()
        ];
        return json($resultData);
    }

    if (!function_exists('getMessageStr')) {
        /**
         * 获取消息中的语言
         * @param string $msg
         * @return mixed|string
         */
        function getMessageStr(string $msg): mixed
        {
            $message = '';
            if (str_contains($msg, "::")) {
                $msgArr = explode('::', $msg);
                foreach ($msgArr as $item) {
                    if (!empty($item)) {
                        $message .= lang($item);
                    }
                }
            } else {
                if (!empty($msg)) $message = lang($msg);
            }
            return $message;
        }
    }
}


