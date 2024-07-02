<?php
/** @noinspection ALL */

namespace Kaadon\ThinkBase\services;

use Redis;
use think\facade\Env;
use Exception;

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

    protected static $static_instance = null;

    /**
     * redisService constructor.
     * @param string|null $host
     * @param int|null $port
     * @param int $select
     * @param string|null $password
     * @throws Exception
     */
    public function __construct(?int $select = null, ?string $host = null, ?int $port = null, ?string $password = null)
    {
        $this->validateConfig($host, $port, $select, $password);

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
     * Get a Redis instance.
     * @return Redis
     * @throws Exception
     */
    public static function instance(): Redis
    {
        if (is_null(self::$static_instance) || !self::$static_instance->ping()) {
            self::$static_instance = self::createRedisInstance();
        }
        return self::$static_instance;
    }

    /**
     * Create and return a Redis instance.
     * @return Redis
     * @throws Exception
     */
    protected static function createRedisInstance(): Redis
    {
        $instance = new self();
        try {
            $redis = $instance->redisClient();
            // Perform a ping to ensure connection and authentication are successful.
            if (!$redis->ping()) {
                throw new Exception('Failed to ping Redis server after connection and authentication.');
            }
            return $redis;
        } catch (Exception $e) {
            throw new Exception('Failed to create Redis instance: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Connect to Redis and return the client.
     * @param int|null $select
     * @return Redis
     * @throws Exception
     */
    protected function redisClient(?int $select = null): Redis
    {
        $redis = new Redis();
        try {
            $redis->connect($this->host, $this->port);
            if (!empty($this->password) && !$redis->auth($this->password)) {
                throw new Exception('Redis authentication failed.');
            }
            if ($select !== null && ($select < 0 || $select > 15)) {
                throw new Exception('Redis database index is out of allowed range (0-15).');
            }
            $redis->select($select ?? $this->select);
            $redis->setOption(\Redis::OPT_PREFIX, Env::get("redis.prefix", "cache:"));
        } catch (Exception $e) {
            throw new Exception('Failed to connect to Redis: ' . $e->getMessage(), 0, $e);
        }
        return $redis;
    }

    /**
     * Validates configuration values.
     * @param string|null $host
     * @param int|null $port
     * @param int $select
     * @param string|null $password
     * @throws Exception
     */
    private function validateConfig(?string $host, ?int $port, int $select, ?string $password)
    {
        if ($port !== null && ($port < 0 || $port > 65535)) {
            throw new Exception('Port number is out of allowed range (0-65535).');
        }
        if ($select < 0 || $select > 15) {
            throw new Exception('Database index is out of allowed range (0-15).');
        }
    }
}
