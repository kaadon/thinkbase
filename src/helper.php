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
        } catch (\Exception) {
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
        $redis = redisService::instance($select);
        $data = $redis->get('cache:' . $name);
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

if (!function_exists('redisCacheUnlink')) {

    /**
     * 删除缓存
     *
     * @param string $name
     * @param int $select
     *
     * @return Redis|int|bool
     * @throws RedisException
     */
    function redisCacheUnlink(string $name, int $select = 1): Redis|int|bool
    {
        return redisService::instance($select)
            ->unlink("cache:" . $name);
    }
}

if (!function_exists('redisCacheDelAll')) {

    /**
     * 批量删除缓存
     *
     * @param string $name
     * @param int $select
     *
     * @return Redis|int|bool
     * @throws RedisException
     */
    function redisCacheDelAll(string $name, int $select = 1): Redis|int|bool
    {
        try {
            //逻辑代码
            $redis = redisService::instance($select);
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

if (!function_exists('paginate')) {
    /**
     * 数据分页处理
     * @param object|array $paginateData
     * @param array $param
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
        return Env::get("app_debug") ?? false;
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
        return Env::get("app_dev") ?? false;
    }
}
	
	if (!function_exists("get_conf")) {
		/**
		 * 获取配置
		 *
		 * @param string                      $group
		 * @param string|null                 $name
		 * @param array|string|int|float|bool $default
		 * @param string                      $filePath
		 *
		 * @return array|string|int|float|bool
		 */
		function get_conf(string $group, ?string $name = null, array|string|int|float|bool $default = "",string $filePath = "app",): array|string|int|float|bool
		{
			$envPath = "{$group}";
			if (!is_null($name)) $envPath .= $name;
			return get_yaconf_config($group,$name,$filePath) ?? (Env::get($envPath) ?: $default);
		}
	}
	
	if (!function_exists('get_yaconf_config')) {
		/**
		 * @param string      $group
		 * @param string|null $name
		 * @param string      $fileName
		 *
		 * @return array|string|int|float|bool
		 */
		function get_yaconf_config(string $group, ?string $name = null, string $fileName = "app"):array|string|int|float|bool
		{
			$path = "{$fileName}.{$group}";
			if (!is_null($name)) $path .= ".{$name}";
			return \Yaconf::get($path);
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
        $message = '';
        if (strpos($msg, "::") !== false) {
            $msgArr = explode('::', $msg);
            foreach ($msgArr as $item) {
                if (!empty($item)) {
                    $message .= lang($item);
                }
            }
        } else {
            if (!empty($msg)) $message = lang($msg);
        }
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
        $message = '';
        if (strpos($msg, "::") !== false) {
            $msgArr = explode('::', $msg);
            foreach ($msgArr as $item) {
                if (!empty($item)) {
                    $message .= lang($item);
                }
            }
        } else {
            if (!empty($msg)) $message = lang($msg);
        }
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
        $message = '';
        if (strpos($msg, "::") !== false) {
            $msgArr = explode('::', $msg);
            foreach ($msgArr as $item) {
                if (!empty($item)) {
                    $message .= lang($item);
                }
            }
        } else {
            if (!empty($msg)) $message = lang($msg);
        }
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
        $message = '';
        if (strpos($msg, "::") !== false) {
            $msgArr = explode('::', $msg);
            foreach ($msgArr as $item) {
                if (!empty($item)) {
                    $message .= lang($item);
                }
            }
        } else {
            if (!empty($msg)) $message = lang($msg);
        }
        $resultData = [
            'code' => $statusCode,
            'message' => empty($message) ? $msg : $message,
            'error' => $err,
            'time' => time()
        ];
        return json($resultData);
    }
}


