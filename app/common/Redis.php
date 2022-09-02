<?php
namespace app\common;

class Redis
{
    private $host = '';
    private $port = '';
    private $pass = '';

    private static $redis;
    
    private function __construct() {
        $this->host = env('REDIS.HOST','127.0.0.1');
        $this->port = env('REDIS.PORT','6379');
        $this->pass = env('REDIS.PASS','123456');
        self::$redis = new \Redis();
        self::$redis->connect($this->host,$this->port);
        self::$redis->auth($this->pass);
    }

    /**
     * 获取连接
     */
    public static function getRedis(){
        if(!self::$redis){
            new self;
        }
        return self::$redis;
    }

    private function __clone(){}
}