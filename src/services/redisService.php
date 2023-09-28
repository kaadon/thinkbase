<?php
/** @noinspection ALL */

namespace Kaadon\ThinkBase\services;

use think\facade\Env;

/**
 *
 */
class redisService
{
    /**
     * @var int|mixed
     */
    private $select = 2;
    /**
     * @var mixed|string
     */
    protected $host = "127.0.0.1";
    /**
     * @var int|mixed
     */
    protected $port = 6379;
    /**
     * @var mixed|string
     */
    protected $password = null;

    protected $static_instance = null;

    /**
     * @param string|null $host
     * @param int|null    $port
     * @param int         $select
     * @param string|null $password
     */
    public function __construct(int $select = 0, ?string $host = null, ?int $port = null, ?string $password = null)
    {
        if (is_null($host)) {
            $this->host = Env::get('redis.hostname', "127.0.0.1");
        }
        if (is_null($port)) {
            $this->port = Env::get('redis.port', 6379);
        }
        if (is_null($select)) {
            $this->select = Env::get('redis.select', 0);
        }
        if (is_null($password)) {
            $this->password = Env::get('redis.password', "");
        }
    }


    /**
     * @param int         $select
     * @param string|null $host
     * @param int|null    $port
     * @param string|null $password
     *
     * @return \Redis
     */
    public static function instance(int $select = 0, ?string $host = null, ?int $port = null, ?string $password = null): \Redis
    {
        if (!isset(self::$static_instance)){
            self::$static_instance = (new self($select, $host, $port, $password))->redisClient();
        }
        return self::$static_instance;
    }

    /**
     * @param $select
     *
     * @return \Redis
     */
    protected function redisClient(?int $select = null): \Redis
    {
        $redis = new \Redis();
        $redis->connect($this->host, $this->port);
        $redis->auth($this->password);
        $redis->select($this->select);
        return $redis;
    }
}