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

if (!function_exists('redisCacheSet')) {

    /**
     * 设置缓存
     *
     * @param string $name
     * @param        $value
     * @param int    $expire
     * @param int    $select
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
     * @param string $name   //key
     * @param int    $select //redis库
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
     * @param int    $select
     *
     * @return Redis|int|bool
     * @throws RedisException
     */
    function redisCacheDel(string $name, int $select = 1): Redis|int|bool
    {
        return redisService::instance($select)->del("cache:" . $name);
    }
}

if (!function_exists('paginate')) {


    /**
     * 数据库返回处理
     *
     * @param Model $data
     *
     * @return array
     */
    function paginate(Model $data): array
    {
        [
            "current_page" => $current_page,
            "data"         => $data,
            "per_page"     => $per_page,
            "last_page"    => $last_page,
            "total"        => $total
        ]
            = $data->toArray();
        return [
            "page"  => $current_page,
            "pages" => $last_page,
            "list"  => $data,
            "limit" => $per_page,
            "count" => $total
        ];
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
