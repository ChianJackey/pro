<?php
namespace app\models;

use think\Model;
use app\models\UserToken;
use think\facade\Config;
use app\common\Redis;
use think\facade\Db;

class User extends Model
{
    public static function getUserInfo($param) {
        return User::where($param)->find();
    }

    /**
     * 前台登录
     * @param string $account登录账号
     * @param string $password用户密码
     */
    public static function login($account, $password){
        $result = self::getUserInfo(['account' => $account]);
        if (empty($result)) {
            return 506;
        }
        $result = $result->toArray();
        $salt = $result['salt'];
        $pass = $result['pass'];
        if(md5($salt . $password . env('LOGIN.RANDOMSTRING')) != $pass){
            return 507;
        }
        /* 登录成功生成token */
        $token = md5($result['id'] . $result['salt'] . time());
        UserToken::reSetToken($result['id'], $token);
        return $token;
    }

    /**
     * 验证用户是否登录
     * @param string $token
     * @return bool
     */
    public static function checkLogin($token){
        $userId = $redis->get('token:' . $token);
        if(!$userId){
            $result = UserToken::where(['token' => $token])->find();
            if(!$result){
                return false;
            }
            $result = $result->toArray();
            $userId = $result['user_id'];

        }

        return false;
    }

    /**
     * 获取权限
     * @param int user_id 如果没有获取全部
     */
    public static function getUserPower($user_id = 0) {
        $where = [];
        if ($user_id > 0) {
            $where['user_id'] = $user_id;
        }
        $result = Db::name('user')
            ->alias('u')
            ->join('department d', 'd.id=u.department_id')
            ->where($where)
            ->field('d.name as department_name, u.name as user_name, u.role_id, u.department_id')
            ->select()
            ->toArray();
        if (empty($result)) {
            return [];
        }
        return $result;
    }

    public static function getUserAll() {
        return User::field('name, id')->select()->toArray();
    }

    public static function getUserData($page, $keywork = ''){
        if ($page != -1) {
            $page = ((int)$page-1) * 10;
            $limit = "LIMIT $page, 15";
        } else {
            $limit = '';
        }
        $where = '';
        if ($keywork != '') {
            $where =  "WHERE u.name LIKE '%$keywork%' OR u.account LIKE '%$keywork%' OR u.id LIKE '%$keywork%'";
        }
        $result = Db::query("SELECT u.id,u.name,u.create_time,count(*) AS count,sum(o .examples_price) AS examples_price,sum(o .discount) AS discount,sum(o.deal_price) AS deal_price,sum(o .chart_price) AS chart_price FROM adverts_user AS u LEFT JOIN adverts_order AS o ON u.id = o.user_id $where GROUP BY u.id ORDER BY u.create_time $limit");
        return $result;
    }

    public static function getUserDataCount($keywork = '') {
        $where = '';
        if ($keywork != '') {
            $where =  "WHERE u.name LIKE '%$keywork%' OR u.account LIKE '%$keywork%' OR u.id LIKE '%$keywork%'";
        }
        return DB::query("SELECT count(*) as count FROM(SELECT u.id,u.name,u.create_time,count(*) AS count,sum(o .examples_price) AS examples_price,sum(o .discount) AS discount,sum(o.deal_price) AS deal_price,sum(o .chart_price) AS chart_price FROM adverts_user AS u LEFT JOIN adverts_order AS o ON u.id = o.user_id $where GROUP BY u.id) AS a");
    }

    public static function chekcUser($account) {
        return Db::name('user')->where(['account' => $account])->count();
    }

    public static function createUser($data) {
        return Db::name('user')->insertGetId($data);
    }

    public static function updateUser($id, $data) {
        Db::name('user')->where(['id' => $id])->update($data);
    }

}