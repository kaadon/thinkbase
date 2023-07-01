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