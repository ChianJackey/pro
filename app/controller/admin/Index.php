<?php
namespace app\controller\admin;

use think\Request;
use think\facade\View;
use app\models\User;
use think\facade\Db;

class Index
{
    /**
     * @param string account 登录账号
     * @param string pass 密码
     */
    public function login(Request $request){
        if($request->method() == 'GET'){
            return View::fetch('Index/login');
        }else{
            $checkField = ['account' => '""@length:2,10', 'pass' => '""@length:6,20'];
            if (!checkValue($response, $checkField)) {
                return $response;
            }
            $result = User::login($checkField['account'], $checkField['pass']);
            var_dump($result);

        }
    }
}