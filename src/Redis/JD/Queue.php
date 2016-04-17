<?php
/**
 * Created by PhpStorm.
 * User: liang
 * Date: 16-4-17
 * Time: 下午7:54
 */

namespace src\Redis\JD;


class Queue
{

    /**
     * 任务队列名称
     * @return string
     */
    public function name()
    {
        return "jd_queue";
    }

    /**
     * 纪录是否完成、重试次数的队列名称
     * @return string
     */
    private function record_name()
    {
        return $this->name() . "_record";
    }

    public function insert($skus, $flag = "undone")
    {
        $this->RedisClient()->multi();

        $key = $this->name();
        foreach ($skus as $sku) {
            $this->RedisClient()->sAdd($key, $sku);
        }

        $key = $this->record_name();
        foreach ($skus as $sku) {
            $this->RedisClient()->hSetNx($key, $sku, $flag);
        }

        $this->RedisClient()->exec();
    }

    public function reinsert($sku)
    {

        $isdone = $this->RedisClient()->hGet($this->record_name(), $sku);
        if ($isdone == "retry_3") {
            return false;

        } else {
            $flag = "retry_1";
            if (($isdone != "undone") && ($isdone != "done")) {
                $isdone = str_replace('retry_', '', $isdone);
                $isdone = intval($isdone) + 1;
                $flag = "retry_" . $isdone;
            }

            $this->insert([$sku], $flag);
            return true;
        }
    }

    public function pop()
    {
        $key = $this->name();
        $sku = $this->RedisClient()->sPop($key);
        return $sku;
    }

    public function finished($sku)
    {
        $key = $this->record_name();
        $this->RedisClient()->hSet($key, $sku, "done");
    }

    /**
     * @var \Redis
     */
    private $RedisClient;

    /**
     * @return \Redis
     */
    private function RedisClient()
    {
        if (!$this->RedisClient) {
            $Redis = new \Redis();
            $Redis->connect(REDIS_HOST);
            $this->RedisClient = $Redis;
        }
        return $this->RedisClient;
    }
}