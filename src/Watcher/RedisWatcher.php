<?php
/**
 * @desc RedisWatcher.php 描述信息
 * @author Tinywan(ShaoBo Wan)
 * @date 2022/1/17 10:02
 */

declare(strict_types=1);

namespace Tinywan\Casbin\Watcher;


use Casbin\Persist\Watcher;
use Closure;
use Workerman\Redis\Client;

class RedisWatcher implements Watcher
{
    private Closure $callback;

    private $pubRedis;

    private $subRedis;

    private $channel;
    /**
     * The config of Watcher.
     *
     * @param array $config
     * [
     *     'host' => '127.0.0.1',
     *     'password' => '',
     *     'port' => 6379,
     *     'database' => 0,
     *     'channel' => '/casbin',
     * ]
     */
    public function __construct(array $config)
    {
        $this->pubRedis = $this->createRedisClient($config);
        $this->subRedis = $this->createRedisClient($config);
        $this->channel = $config['channel'] ?? '/casbin';

        $this->subRedis->subscribe([$this->channel], function ($channel, $message) {
            if ($this->callback) {
                call_user_func($this->callback);
            }
        });
    }

    /**
     * Sets the callback function that the watcher will call when the policy in DB has been changed by other instances.
     * A classic callback is loadPolicy() method of Enforcer class.
     *
     * @param Closure $func
     */
    public function setUpdateCallback(Closure $func): void
    {
        $this->callback = $func;
    }

    /**
     * Update calls the update callback of other instances to synchronize their policy.
     * It is usually called after changing the policy in DB, like savePolicy() method of Enforcer class,
     * addPolicy(), removePolicy(), etc.
     */
    public function update(): void
    {
        $this->pubRedis->publish($this->channel, 'casbin rules updated');
    }

    /**
     * Close stops and releases the watcher, the callback function will not be called any more.
     */
    public function close(): void
    {
        $this->pubRedis->close();
        $this->subRedis->close();
    }

    /**
     * Create redis client
     *
     * @param array $config
     * @return Client
     */
    private function createRedisClient(array $config): Client
    {
        $config['host'] = $config['host'] ?? '127.0.0.1';
        $config['port'] = $config['port'] ?? 6379;
        $config['password'] = $config['password'] ?? '';
        $config['database'] = $config['database'] ?? 0;

        $redis = new Client('redis://' . $config['host'] . ':' . $config['port']);
        $redis->auth($config['password'] ?? '');

        return $redis;
    }
}