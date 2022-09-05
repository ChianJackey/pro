<?php
declare (strict_types = 1);

namespace app\middleware;

use think\Request;
use app\models\UserToken;

class CheckToken
{
    /**
     * 处理请求
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next) {
        // $get = $request->get();
        // $response = redirect((string) url('login'));
        // if(isset($get['token'])) {
        //     $token = $hearer['token'];
        //     $result = UserToken::checkToken($token);
        //     if (!$result) {
        //         return $response;
        //     }
        //     return $next($request);
        // }
        // return $response;
        return $next($request);
    }
}
