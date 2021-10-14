<?php declare(strict_types=1);

namespace App\Service;

use App\Common\Traits\StaticInstance;
use Swoft\Redis\Redis;

/**
 * redis服务
 * Class RedisService
 * @package App\Service
 */
class RedisService
{

    use StaticInstance;
    /**
     * 获取redis连接
     * @param int $db
     * @return \Swoft\Redis\Connection\Connection
     */
    public function getConnect($db = 0)
    {
        return Redis::connection('redis_' . $db . '.pool');
    }
}
