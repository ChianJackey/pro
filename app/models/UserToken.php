<?php
namespace app\models;

use app\common\Redis;
use app\models\User;
use think\facade\Config;
use think\Model;

class UserToken extends Model
{
    /**
     * 更新token
     */
    public static function reSetToken($userId, $token) {
        $result = UserToken::where(['user_id' => $userId])->find();
        $time = time();
        if ($result == null) {
            UserToken::create(['user_id' => $userId, 'token' => $token, 'create_time' => $time]);
        } else {
            $result->token = $token;
            $result->create_time = $time;
            $result->save();
        }
        $redis = Redis::getRedis();
        $redis->set('token:' . $token,$userId,Config::get('app.token_expire'));
    }

    public static function checkToken($token) {
        $redis = Redis::getRedis();
        $userId = $redis->get('token:' . $token);
        if(!$userId){
            return false;
        }
        $redis->set('token:' . $token,$userId,Config::get('app.token_expire'));
        return true;
    }

}