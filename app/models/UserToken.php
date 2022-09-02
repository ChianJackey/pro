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
        $redis->set('token:'.$token,$userId,Config::get('app.token_expire'));
    }

    public static function checkToken($token) {
        $result = UserToken::where(['token' => $token])->find();
        if ($result === null) {
            return false;
        }
        $token_expire = Config::get('app.token_expire'); 
        if ($result['create_time'] + $token_expire < time()) {
            return false;
        }
        return User::getUserInfo(['id' => $result['user_id']]);
    }

}