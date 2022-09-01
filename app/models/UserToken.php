<?php
namespace app\models;

use app\models\User;
use think\facade\Config;
use think\Model;

class UserToken extends Model
{
    public static function reSetToken($user_id, $run_type, $token) {
        $result = UserToken::where(['user_id' => $user_id, 'run_type' => $run_type])->find();
        $time = time();
        if ($result == null) {
            UserToken::create(['user_id' => $user_id, 'run_type' => $run_type, 'token' => $token, 'create_time' => $time]);
        } else {
            $result->token = $token;
            $result->create_time = $time;
            $result->save();
        }
    }

    public static function checkToken($token, $run_type = 2) {
        $result = UserToken::where(['token' => $token])->find();
        if ($result === null) {
            return false;
        }
        if ($result['run_type'] !== $run_type) {
            return false;
        }
        $token_expire = Config::get('app.token_expire'); 
        if ($result['create_time'] + $token_expire < time()) {
            return false;
        }
        return User::getUserInfo(['id' => $result['user_id']]);
    }

}